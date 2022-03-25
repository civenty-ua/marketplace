<?php
declare(strict_types=1);

namespace App\Repository\Market;

use Doctrine\ORM\{
    NoResultException,
    NonUniqueResultException,
    EntityManager,
    QueryBuilder,
};
use Doctrine\ORM\Query\Expr\Join;
use App\Repository\Market\Exception\CommoditiesEmptyFilterException;
use App\Repository\Helper\AliasMap;
use App\Repository\Traits\FilterApplierTrait;
use App\Entity\{
    Region,
    User,
    Market\CategoryAttributeParameters,
    Market\CommodityFavorite,
    Market\UserCertificate,
    Market\UserProperty,
    Market\Phone,
};
use function is_bool;

/**
 * Commodity repository trait.
 *
 * Dictates basic interface for all commodities repositories, provides basic functionality.
 *
 * @method QueryBuilder     createQueryBuilder(string $alias)
 * @method string           getEntityName()
 * @method EntityManager    getEntityManager()
 */
trait CommodityRepositoryCommonInterfaceTrait
{
    use FilterApplierTrait;

    /**
     * Get available sort values for list filter.
     *
     * @return string[]                     Available sort values.
     */
    public static function getListFilterAvailableSortValues(): array
    {
        return [
            'createdDate',
            'priceDesc',
            'priceAsc',
        ];
    }

    /**
     * List filter provider.
     *
     * @param User|null $user Current user.
     * @param string|null $order Order.
     * @param array $filter Filter.
     * @param array $filterAttributes Filter (attributes).
     * @param CategoryAttributeParameters[] $attributesParameters Category attributes parameters.
     *
     * @return  QueryBuilder                                            Query builder.
     */
    public function listFilter(
        ?User $user,
        ?string $order,
        array $filter = [],
        array $filterAttributes = [],
        array $attributesParameters = []
    ): QueryBuilder
    {
        $repositoryEntity = $this->getEntityName();
        $aliasMap = (new AliasMap())
            ->setAlias($repositoryEntity, 'rootCommodity');
        $queryBuilder = $this
            ->createQueryBuilder($aliasMap->getAlias($repositoryEntity))
            ->addSelect($aliasMap->getAlias($repositoryEntity));
        $activityFilter = (array)($filter['activity'] ?? []);

        $this->applyAdditionalJoinsAndSelects($queryBuilder, $aliasMap);
        $this->applyFavoritesJoinsAndSelects($queryBuilder, $aliasMap, $user);

        $this->applyActivityFilter($queryBuilder, $aliasMap, $activityFilter);
        $this->applyOrder($queryBuilder, $order);

        try {
            $this->applyBasicFilter($queryBuilder, $aliasMap, $filter);
            $this->applyAttributesFilter(
                $queryBuilder,
                $filterAttributes,
                $attributesParameters
            );
        } catch (CommoditiesEmptyFilterException $exception) {

        }

        return $queryBuilder;
    }

    /**
     * Get total count, using list filter.
     *
     * @param User|null $user Current user.
     * @param array $filter Filter.
     * @param array $filterAttributes Filter (attributes).
     * @param CategoryAttributeParameters[] $attributesParameters Category attributes parameters.
     *
     * @return  int                                                     Total count.
     */
    public function getTotalCount(
        ?User $user,
        array $filter = [],
        array $filterAttributes = [],
        array $attributesParameters = []
    ): int
    {
        $queryBuilder = $this->listFilter(
            $user,
            null,
            $filter,
            $filterAttributes,
            $attributesParameters
        );
        $alias = $queryBuilder->getRootAliases()[0];

        return count($queryBuilder
            ->select("$alias.id")
            ->groupBy("$alias.id")
            ->getQuery()
            ->getResult());
    }

    /**
     * Find and get exist max price.
     *
     * @return float                        Max price.
     */
    public function getMaxPrice(): float
    {
        try {
            $queryBuilder = $this->listFilter(null, null, [
                'activity' => true,
            ]);
            $alias = $queryBuilder->getRootAliases()[0];

            return (float)$queryBuilder
                    ->select("$alias.price")
                    ->orderBy("$alias.price", 'DESC')
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getSingleResult()['price'] ?? 0;
        } catch (NoResultException | NonUniqueResultException $exception) {
            return 0;
        }
    }

    /**
     * Apply additional joins and selects.
     *
     * @param QueryBuilder $queryBuilder Query builder.
     * @param AliasMap $aliasMap Alias map.
     *
     * @return  void
     */
    protected function applyAdditionalJoinsAndSelects(
        QueryBuilder $queryBuilder,
        AliasMap $aliasMap
    ): void
    {
        $aliasMap
            ->setAlias(User::class, 'commodityUser')
            ->setAlias(Phone::class, 'commodityUserPhone')
            ->setAlias(Region::class, 'commodityUserRegion')
            ->setAlias(UserProperty::class, 'commodityUserProperty')
            ->setAlias(UserCertificate::class, 'commodityUserCertificates');

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $userAlias = $aliasMap->getAlias(User::class);
        $userPropertyAlias = $aliasMap->getAlias(UserProperty::class);

        $queryBuilder
            ->leftJoin("$rootAlias.user", $aliasMap->getAlias(User::class))
            ->addSelect($aliasMap->getAlias(User::class))
            ->leftJoin("$userAlias.phones", $aliasMap->getAlias(Phone::class))
            ->addSelect($aliasMap->getAlias(Phone::class))
            ->leftJoin("$userAlias.region", $aliasMap->getAlias(Region::class))
            ->addSelect($aliasMap->getAlias(Region::class))
            ->leftJoin("$userAlias.userProperty", $aliasMap->getAlias(UserProperty::class))
            ->addSelect($aliasMap->getAlias(UserProperty::class))
            ->leftJoin(
                "$userPropertyAlias.userCertificates",
                $aliasMap->getAlias(UserCertificate::class)
            )
            ->addSelect($aliasMap->getAlias(UserCertificate::class));
    }

    /**
     * Apply activity filter.
     *
     * @param QueryBuilder $queryBuilder Query builder.
     * @param AliasMap $aliasMap Alias map.
     * @param bool[] $filter Activity filter.
     *
     * @return  void
     */
    abstract protected function applyActivityFilter(
        QueryBuilder $queryBuilder,
        AliasMap $aliasMap,
        array $filter
    ): void;

    /**
     * Apply order.
     *
     * @param QueryBuilder $queryBuilder Query builder.
     * @param string|null $order Order.
     *
     * @return  void
     */
    protected function applyOrder(QueryBuilder $queryBuilder, ?string $order): void
    {
        $alias = $queryBuilder->getRootAliases()[0];

        switch ($order) {
            case 'priceDesc':
                $queryBuilder->orderBy("$alias.price", 'desc');
                break;
            case 'priceAsc':
                $queryBuilder->orderBy("$alias.price", 'asc');
                break;
            case 'createdDate':
                $queryBuilder->orderBy("$alias.createdAt", 'desc');
                break;
            case 'id':
                $queryBuilder->orderBy("$alias.id", 'desc');
                break;
            case null:
            default:
        }
    }

    /**
     * Apply basic filter.
     *
     * @param QueryBuilder $queryBuilder Query builder.
     * @param AliasMap $aliasMap Alias map.
     * @param array $filter Filter.
     *
     * @return  void
     * @throws  CommoditiesEmptyFilterException No filters actions were fired.
     */
    protected function applyBasicFilter(
        QueryBuilder $queryBuilder,
        AliasMap $aliasMap,
        array $filter
    ): void
    {
        $alias = $queryBuilder->getRootAliases()[0];
        $priceFrom = (int)($filter['price'][0] ?? 0);
        $priceTo = (int)($filter['price'][1] ?? 0);
        $inFavorite = (bool)($filter['inFavorite'] ?? false);
        $filterToApply = [];
        $anyFilterExist = false;

        if (isset($filter['id'])) {
            $filterToApply['id'] = $filter['id'];
            $anyFilterExist = true;
        }
        if (isset($filter['!id'])) {
            $filterToApply[] = [
                'field' => 'id',
                'condition' => '!=',
                'value' => $filter['!id'],
            ];
            $anyFilterExist = true;
        }
        if (isset($filter['slug'])) {
            $queryBuilder
                ->andWhere(
                    $queryBuilder->expr()->eq("$alias.slug", ':slug')
                )
                ->setParameter('slug', $filter['slug']);
            $anyFilterExist = true;
        }
        if (isset($filter['!slug'])) {
            $queryBuilder
                ->andWhere(
                    $queryBuilder->expr()->neq("$alias.slug", ':notSlug')
                )
                ->setParameter('notSlug', $filter['!slug']);
            $anyFilterExist = true;
        }
        if (isset($filter['active']) && is_bool($filter['active'])) {
            if ($filter['active'] === true) {
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->eq("$alias.isActive", 1)
                );
            } else {
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->orX(
                        $queryBuilder->expr()->eq("$alias.isActive", 0),
                        $queryBuilder->expr()->isNull("$alias.isActive")
                    )
                );
            }

            $anyFilterExist = true;
        }
        if (isset($filter['user'])) {
            $queryBuilder
                ->andWhere(
                    $queryBuilder->expr()->eq("$alias.user", ':commodityUser')
                )
                ->setParameter('commodityUser', $filter['user']);
            $anyFilterExist = true;
        }
        if (isset($filter['!user'])) {
            $filterToApply[] = [
                'field' => 'user',
                'condition' => '!=',
                'value' => $filter['!user'],
            ];
            $anyFilterExist = true;
        }
        if ($priceFrom > 0) {
            $filterToApply[] = [
                'field' => 'price',
                'condition' => '>=',
                'value' => $priceFrom,
            ];
            $anyFilterExist = true;
        }
        if ($priceTo > 0) {
            $filterToApply[] = [
                'field' => 'price',
                'condition' => '<=',
                'value' => $priceTo,
            ];
            $anyFilterExist = true;
        }
        if ($inFavorite) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->isNotNull("{$aliasMap->getAlias(CommodityFavorite::class)}.id")
            );
            $anyFilterExist = true;
        }

        if (!$anyFilterExist) {
            throw new CommoditiesEmptyFilterException();
        }

        $this->applyFilter($queryBuilder, $alias, $filterToApply);
    }

    /**
     * Apply attributes filters.
     *
     * @param QueryBuilder $queryBuilder Query builder.
     * @param array $filter Commodities filter.
     * @param CategoryAttributeParameters[] $attributesParameters Category attributes parameters.
     *
     * @return  void
     * @throws  CommoditiesEmptyFilterException                         No filters actions were fired.
     */
    abstract protected function applyAttributesFilter(
        QueryBuilder $queryBuilder,
        array $filter,
        array $attributesParameters
    ): void;

    /**
     * Apply additional joins and selects (favorites).
     *
     * @param QueryBuilder $queryBuilder Query builder.
     * @param AliasMap $aliasMap Alias map.
     * @param User|null $user User, if exists.
     *
     * @return  void
     */
    private function applyFavoritesJoinsAndSelects(
        QueryBuilder $queryBuilder,
        AliasMap $aliasMap,
        ?User $user
    ): void
    {
        $aliasMap->setAlias(CommodityFavorite::class, 'commodityFavorite');

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $favoritesAlias = $aliasMap->getAlias(CommodityFavorite::class);

        $queryBuilder
            ->leftJoin(
                "$rootAlias.favorites",
                $aliasMap->getAlias(CommodityFavorite::class),
                Join::WITH,
                "$favoritesAlias.user = :currentUser"
            )
            ->addSelect($favoritesAlias)
            ->setParameter('currentUser', $user ? $user->getId() : 0);
    }
}
