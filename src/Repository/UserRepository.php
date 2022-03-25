<?php
declare(strict_types=1);

namespace App\Repository;

use Throwable;
use DateTime;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Security\Core\{
    Exception\UnsupportedUserException,
    User\PasswordUpgraderInterface,
    User\UserInterface,
};
use App\Repository\Traits\{
    FilterApplierTrait,
    CountPerCreatedDateTrait,
    UserHelperTrait,
};
use App\Repository\Helper\AliasMap;
use App\Entity\{Crop, Region, User, Market\Commodity, Market\UserFavorite};
use function count;
use function get_class;
use function in_array;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    use FilterApplierTrait;
    use CountPerCreatedDateTrait;
    use UserHelperTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function countMainDashboardUserFilter(array $filter)
    {
        $builder = $this
            ->createQueryBuilder('user')
            ->select('count(user.id)');
        if (isset($filter['today']) && $filter['today'] === true) {
            $dateToday = new DateTime('today');
            $dateTomorrow = new DateTime('tomorrow');
            $builder->andWhere($builder->expr()->between('user.createdAt', ':from', ':to'));
            $builder->setParameters([
                'from' => $dateToday->format('Y-m-d H:i:s'),
                'to' => $dateTomorrow->format('Y-m-d H:i:s'),
            ]);
        }
        if (isset($filter['gender']) && count($filter['gender'])) {
            $orX = $builder->expr()->orX();
            foreach ($filter['gender'] as $gender) {
                if ($gender === null) {
                    $orX->add($builder->expr()->isNull('user.gender'));
                } else {
                    $orX->add($builder->expr()->eq('user.gender', ':gender'));
                    $builder->setParameter('gender', $gender);
                }
            }
            $builder->andWhere($orX);
        }
        if (isset($filter['isBanned'])) {
            $builder->andWhere(
                $builder->expr()->eq( 'user.isBanned' , ':isBanned')
            );
            $builder->setParameter('isBanned', $filter['isBanned']);
        }
        if (isset($filter['!roles']) && count($filter['!roles'])) {
            $andX = $builder->expr()->andX();
            foreach ($filter['!roles'] as $role) {
                $andX->add($builder->expr()->notLike('user.roles', "'%$role%'"));
            }
            $builder->andWhere($andX);
        }
        try {
            return $builder
                ->getQuery()
                ->getSingleScalarResult();
        } catch (Throwable $exception) {
            return 0;
        }
    }

    /**
     * Get items count per time.
     *
     * @param DateTime $from Date from.
     * @param DateTime $to Date to.
     * @param array $filter Filter.
     *
     * @return  array                       Data, where
     *                                      key is date and
     *                                      value is items count for that day.
     */
    public function getRegistrationsCountPerTime(DateTime $from, DateTime $to, array $filter = []): array
    {
        return $this->getItemsCountPerCreatedDate($from, $to, $filter);
    }

    /**
     * Get users count per regions.
     *
     * @param string $locale Locale.
     *
     * @return  array                       Data, where
     *                                      key is region and
     *                                      value is users count for that regions.
     */
    public function getCountPerRegions(string $locale, array $filter): array
    {
        $aliasUser = 'user';
        $aliasRegion = 'region';
        $aliasRegionTranslation = "{$aliasRegion}_translation";
        $data = $this->createQueryBuilder($aliasUser);
        if (isset($filter['!roles']) && count($filter['!roles'])) {
            $andX = $data->expr()->andX();
            foreach ($filter['!roles'] as $role) {
                $andX->add($data->expr()->notLike("{$aliasUser}.roles", "'%$role%'"));
            }
            $data->andWhere($andX);
        }
        if (isset($filter['isBanned'])) {
            $data->andWhere(
                $data->expr()->eq( "{$aliasUser}.isBanned" , ':isBanned')
            );
            $data->setParameter('isBanned', $filter['isBanned']);
        }
        $data
            ->leftJoin(
                Region::class,
                $aliasRegion,
                Join::WITH,
                "$aliasUser.region = $aliasRegion.id"
            )
            ->leftJoin(
                "$aliasRegion.translations",
                $aliasRegionTranslation,
                Join::WITH,
                "$aliasUser.region = $aliasRegion.id"
            )
            ->select([
                "$aliasRegionTranslation.name as regionName",
            ])
            ->andWhere("$aliasRegionTranslation.locale = :locale")
            ->setParameter('locale', $locale);
        $result = [];

        foreach ($data->getQuery()->getResult() as $item) {
            $regionName = $item['regionName'];
            $result[$regionName] = $result[$regionName] ?? 0;
            $result[$regionName]++;
        }
        return $result;
    }

    /**
     * Market list filter provider.
     *
     * @param User|null $user User.
     * @param string|null $order Order.
     * @param array $filter Filter.
     *
     * @return  QueryBuilder                Query builder.
     */
    public function marketListFilter(?User $user, ?string $order, array $filter = []): QueryBuilder
    {
        $aliasMap = (new AliasMap())
            ->setAlias(User::class, 'user');
        $rootAlias = $aliasMap->getAlias(User::class);
        $queryBuilder = $this
            ->createQueryBuilder($rootAlias)
            ->addSelect($rootAlias);

        $this->applyUserSubEntitiesQuery($queryBuilder, $aliasMap);
        $this->applyUsersFavoritesQuery($queryBuilder, $user, $aliasMap);

        $this->applyActivityFilter($queryBuilder, $filter);
        $this->applyMarketListOrder($queryBuilder, $order);
        $this->applyMarketListFilter($queryBuilder, $filter, $aliasMap);

        return $queryBuilder;
    }

    /**
     * Get favorites count for given user.
     *
     * @param User|null $user User.
     * @param array $filter Filter.
     *
     * @return  int                         Favorites count.
     */
    public function getTotalCount(?User $user, array $filter = []): int
    {
        $queryBuilder = $this->marketListFilter($user, null, $filter);
        $alias = $queryBuilder->getRootAliases()[0];

        return count($queryBuilder
            ->select("$alias.id")
            ->groupBy("$alias.id")
            ->getQuery()
            ->getResult());
    }

    /**
     * Apply activity filter.
     *
     * @param QueryBuilder $queryBuilder Query builder.
     * @param array $filter Filter.
     *
     * @return  void
     */
    private function applyActivityFilter(QueryBuilder $queryBuilder, array $filter): void
    {
        $activity = (array)($filter['activity'] ?? []);

        if (in_array(true, $activity, true) && in_array(false, $activity, true)) {
            return;
        }
        if (in_array(false, $activity, true)) {
            $this->applyMarketUserInactivityFilter($queryBuilder);
        } else {
            $this->applyMarketUserActivityFilter($queryBuilder);
        }
    }

    /**
     * Apply market list order.
     *
     * @param QueryBuilder $queryBuilder Query builder.
     * @param string|null $order Order.
     *
     * @return  void
     */
    private function applyMarketListOrder(QueryBuilder $queryBuilder, ?string $order): void
    {
        $alias = $queryBuilder->getRootAliases()[0];

        switch ($order) {
            case 'id':
                $queryBuilder->orderBy("$alias.id", 'asc');
                break;
            case null:
            default:
        }
    }

    /**
     * Apply products list filters.
     *
     * @param QueryBuilder $queryBuilder Query builder.
     * @param array $filter Filter.
     * @param AliasMap $aliasMap Alias map.
     *
     * @return  void
     */
    private function applyMarketListFilter(
        QueryBuilder $queryBuilder,
        array        $filter,
        AliasMap     $aliasMap
    ): void
    {
        $alias = $queryBuilder->getRootAliases()[0];
        $inFavorite = (bool)($filter['inFavorite'] ?? false);
        $search = trim($filter['search'] ?? '');
        $filterToApply = [];

        if (isset($filter['region'])) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq(
                    "{$aliasMap->getAlias(User::class)}.region",
                    ':regionId'
                )
            );
            $queryBuilder->setParameter('regionId', $filter['region']);
        }
        if (isset($filter['crop'])) {
            $aliasMap->setAlias(Crop::class, 'crop');
            $queryBuilder->leftJoin(
                "{$aliasMap->getAlias(User::class)}.crops",
                $aliasMap->getAlias(Crop::class)
            );

            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq(
                    "{$aliasMap->getAlias(Crop::class)}.id",
                    ':cropId'
                )
            );
            $queryBuilder->setParameter('cropId', $filter['crop']);
        }
        if (isset($filter['created_at'])) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->lte("{$aliasMap->getAlias(User::class)}.createdAt", ':date'))
                ->setParameter('date', $filter['created_at']);
        }
        if (isset($filter['gender'])) {
            $filterToApply['gender'] = $filter['gender'];
        }
        if (isset($filter['!roles'])) {
            $this->exceptRolesFilter($queryBuilder, (array)$filter['!roles']);
        }
        if (isset($filter['roles'])) {
            $this->setRolesFilter($queryBuilder, (array)$filter['roles']);
        }
        if (isset($filter['id'])) {
            $filterToApply['id'] = $filter['id'];
        }
        if (isset($filter['!id'])) {
            $filterToApply[] = [
                'field' => 'id',
                'condition' => '!=',
                'value' => $filter['!id'],
            ];
        }
        if ($inFavorite) {
            $queryBuilder->andWhere("{$aliasMap->getAlias(UserFavorite::class)}.id IS NOT NULL");
        }
        if (isset($filter['commodities'])) {
            $commodities = (array)($filter['commodities'] ?? []);
            $aliasMap->setAlias(Commodity::class, 'userCommodities');

            $queryBuilder
                ->leftJoin("$alias . commodities", $aliasMap->getAlias(Commodity::class))
                ->andWhere("{$aliasMap->getAlias(Commodity::class)}.id IN(:userCommodities)")
                ->setParameter('userCommodities', count($commodities) > 0 ? $commodities : ['none']);
        }
        if (strlen($search) > 0) {
            $queryBuilder
                ->andWhere(
                    $queryBuilder->expr()->orX(
                        $queryBuilder->expr()->like("$alias . name", ':searchString'),
                        $queryBuilder->expr()->eq("$alias . email", ':searchEmail'),
                        $queryBuilder->expr()->eq("$alias . id", ':searchId'),
                    )
                )
                ->setParameter('searchString', " % $search % ")
                ->setParameter('searchEmail', $search)
                ->setParameter('searchId', (int)$search);
        }
        $this->applyFilter($queryBuilder, $alias, $filterToApply);
    }

    /**
     * Set user roles filter
     *
     * @param QueryBuilder $queryBuilder Query builder.
     * @param string[] $roles Roles filter.
     *
     * @return  void
     */
    private function setRolesFilter(QueryBuilder $queryBuilder, array $roles): void
    {
        if (count($roles) === 0) {
            return;
        }
        $alias = $queryBuilder->getRootAliases()[0];
        $orX = $queryBuilder->expr()->orX();
        foreach ($roles as $role) {
            $orX->add($queryBuilder->expr()->like("$alias . roles", "'%$role%'"));
        }

        $queryBuilder->andWhere($orX);
    }


    /**
     * Exclude users with these roles filter
     *
     * @param QueryBuilder $queryBuilder Query builder.
     * @param string[] $roles Roles filter.
     *
     * @return  void
     */
    private function exceptRolesFilter(QueryBuilder $queryBuilder, array $roles): void
    {
        if (count($roles) === 0) {
            return;
        }
        $alias = $queryBuilder->getRootAliases()[0];
        $andX = $queryBuilder->expr()->andX();
        foreach ($roles as $role) {
            $andX->add($queryBuilder->expr()->notLike("$alias . roles", "'%$role%'"));
        }

        $queryBuilder->andWhere($andX);
    }
}
