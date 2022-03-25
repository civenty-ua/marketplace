<?php
declare(strict_types = 1);

namespace App\Repository\Market;

use DateTime;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Repository\Traits\{
    IdSelectorTrait,
    Market\CommodityHelperTrait,
};
use App\Repository\Market\Exception\{
    CommoditiesEmptyFilterException,
    QueryEmptyResultException,
};
use App\Repository\Helper\AliasMap;
use App\Entity\{
    Market\Commodity,
    Market\CommodityProduct,
    Market\CommodityService,
    Market\CommodityKit,
};
/**
 * @method CommodityKit|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommodityKit|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommodityKit[]    findAll()
 * @method CommodityKit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommodityKitRepository extends ServiceEntityRepository
{
    use IdSelectorTrait;
    use CommodityHelperTrait;
    use CommodityRepositoryCommonInterfaceTrait {
        CommodityRepositoryCommonInterfaceTrait::applyAdditionalJoinsAndSelects  as parentApplyAdditionalJoinsAndSelects;
        CommodityRepositoryCommonInterfaceTrait::applyBasicFilter                as parentApplyBasicFilter;
    }

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommodityKit::class);
    }

    public function getActiveKitsThatHasExpiredCommodities(Commodity $commodity)
    {
        $sqlRaw = "SELECT commodity_kit_id FROM commodity_kit_commodity WHERE commodity_id ={$commodity->getId()}";
        $statement = $this->getEntityManager()->getConnection()->prepare($sqlRaw);
        $statement->execute();
        $result = $statement->fetchAll();

        $ids = [];
        foreach ($result as $item) {
            $ids[] = $item['commodity_kit_id'];
        }

        return $this->createQueryBuilder('c')
            ->where('c.id IN (:ids)')
            ->andWhere('c.isActive = true')
            ->andWhere('c.activeFrom <= :now')
            ->andWhere('c.activeTo > :now')
            ->setParameter('ids',$ids)
            ->setParameter('now', new DateTime('now'))
            ->getQuery()
            ->getResult();
    }
    /**
     * @inheritDoc
     */
    protected function applyAdditionalJoinsAndSelects(
        QueryBuilder    $queryBuilder,
        AliasMap        $aliasMap
    ): void {
        $this->parentApplyAdditionalJoinsAndSelects($queryBuilder, $aliasMap);

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $aliasMap
            ->setAlias(Commodity::class, 'kitCommodities');

        $queryBuilder
            ->leftJoin("$rootAlias.commodities", $aliasMap->getAlias(Commodity::class))
            ->addSelect($aliasMap->getAlias(Commodity::class));
    }
    /**
     * @inheritDoc
     */
    protected function applyActivityFilter(
        QueryBuilder    $queryBuilder,
        AliasMap        $aliasMap,
        array           $filter
    ): void {
        if (in_array(true, $filter) && in_array(false, $filter)) {
            return;
        }

        if (in_array(false, $filter)) {
            $this->applyCommoditiesInactivityFilter(
                $queryBuilder,
                Commodity::TYPE_KIT,
                $aliasMap
            );
        } else {
            $this->applyCommoditiesActivityFilter(
                $queryBuilder,
                Commodity::TYPE_KIT,
                $aliasMap
            );
        }
    }
    /**
     * @inheritDoc
     */
    protected function applyBasicFilter(
        QueryBuilder    $queryBuilder,
        AliasMap        $aliasMap,
        array           $filter
    ): void {
        try {
            $this->parentApplyBasicFilter($queryBuilder, $aliasMap, $filter);
        } catch (CommoditiesEmptyFilterException $exception) {

        }

        $alias  = $queryBuilder->getRootAliases()[0];
        $search = trim($filter['search'] ?? '');

        if (strlen($search) > 0) {
            $queryBuilder
                ->andWhere("$alias.title LIKE :search OR $alias.id = :search")
                ->setParameter('search', "%$search%");
        }
        if (isset($filter['participant'])) {
            /** @var QueryBuilder $subQueryBuilder */
            $subQueryBuilder    = $this
                ->getEntityManager()
                ->getRepository(Commodity::class)
                ->listFilter(null, null, [
                    'user'      => $filter['participant'],
                    'activity'  => [true, false],
                ]);
            $foundCommodities   = $this->queryIdSet($subQueryBuilder);
            $filteredKitsId     = $this->getKitsWithIncludedCommodities($foundCommodities);

            $queryBuilder
                ->andWhere("$alias.user = :participant OR $alias.id IN (:participantCommodities)")
                ->setParameter('participant', $filter['participant'])
                ->setParameter('participantCommodities', $filteredKitsId);
        }
    }
    /**
     * @inheritDoc
     */
    protected function applyAttributesFilter(
        QueryBuilder    $queryBuilder,
        array           $filter,
        array           $attributesParameters
    ): void {
        $aliasKit       = $queryBuilder->getRootAliases()[0];
        $filteredKitsId = [];

        foreach ($filter as $commodityFilter) {
            $commodityType              = $commodityFilter['commodityType'] ?? '';
            $commodityAttributesFilter  = $commodityFilter['attributes']    ?? [];
            unset(
                $commodityFilter['attributes'],
                $commodityFilter['commodityType']
            );

            switch ($commodityType) {
                case Commodity::TYPE_PRODUCT:
                    $repositoryEntity = CommodityProduct::class;
                    break;
                case Commodity::TYPE_SERVICE:
                    $repositoryEntity = CommodityService::class;
                    break;
                default:
                    continue 2;
            }

            try {
                $foundCommodities   = $this
                    ->getEntityManager()
                    ->getRepository($repositoryEntity)
                    ->listFilterKits(
                        $commodityFilter,
                        $commodityAttributesFilter,
                        $attributesParameters
                    );
                $filteredKitsId[]   = $this->getKitsWithIncludedCommodities($foundCommodities);
            } catch (QueryEmptyResultException $exception) {
                $this->applyFilter($queryBuilder, $aliasKit, [
                    'id' => 'none',
                ]);
                return;
            } catch (CommoditiesEmptyFilterException $exception) {

            }
        }

        if (count($filteredKitsId) === 0) {
            return;
        }

        $needKitsId = count($filteredKitsId) === 1
            ? $filteredKitsId[0]
            : call_user_func_array('array_intersect', $filteredKitsId);

        $this->applyFilter($queryBuilder, $aliasKit, [
            'id' => count($needKitsId) > 0 ? $needKitsId : 'none',
        ]);
    }
    /**
     * Find kits ID with included commodities.
     *
     * @param   int[] $commodities          Commodities ID set.
     *
     * @return  int[]                       Kits ID set.
     */
    private function getKitsWithIncludedCommodities(array $commodities): array
    {
        $kitAlias           = 'kit';
        $commoditiesAlias   = 'kitCommodities';
        $queryBuilder       = $this
            ->createQueryBuilder($kitAlias)
            ->leftJoin("$kitAlias.commodities", $commoditiesAlias)
            ->where("$commoditiesAlias.id in (:commodities)")
            ->setParameter('commodities', $commodities);

        return $this->queryIdSet($queryBuilder);
    }
}
