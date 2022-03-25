<?php
declare(strict_types=1);

namespace App\Repository\Market;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Repository\Market\Exception\CommoditiesEmptyFilterException;
use App\Repository\Helper\AliasMap;
use App\Repository\Traits\IdSelectorTrait;
use App\Entity\Market\{
    Commodity,
    CommodityProduct,
    CommodityService,
    CommodityKit,
};
/**
 * @method Commodity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Commodity|null findOneBy(array $criteria, array $orderBy = null)
 * @method Commodity[]    findAll()
 * @method Commodity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommodityRepository extends ServiceEntityRepository
{
    use IdSelectorTrait;
    use CommodityRepositoryCommonInterfaceTrait {
        CommodityRepositoryCommonInterfaceTrait::applyBasicFilter as parentApplyBasicFilter;
    }

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commodity::class);
    }
    /**
     * @inheritDoc
     */
    protected function applyActivityFilter(
        QueryBuilder    $queryBuilder,
        AliasMap        $aliasMap,
        array           $filter
    ): void {

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

        $rootAlias              = $queryBuilder->getRootAliases()[0];
        $commoditiesTypesFilter = (array) ($filter['commodityType'] ?? []);
        $commoditiesId          = [];

        foreach ([
            Commodity::TYPE_PRODUCT => CommodityProduct::class,
            Commodity::TYPE_SERVICE => CommodityService::class,
            Commodity::TYPE_KIT     => CommodityKit::class,
        ] as $commodityType => $repository) {
            if (count($commoditiesTypesFilter) > 0 && !in_array($commodityType, $commoditiesTypesFilter)) {
                continue;
            }

            /** @var QueryBuilder $queryBuilder */
            $subCommoditiesQueryBuilder = $this
                ->getEntityManager()
                ->getRepository($repository)
                ->listFilter(null, null, $filter);
            $commoditiesId              = array_merge(
                $commoditiesId,
                $this->queryIdSet($subCommoditiesQueryBuilder)
            );
        }
        $commoditiesId = array_unique($commoditiesId);

        $queryBuilder
            ->andWhere("$rootAlias.id IN (:commodities)")
            ->setParameter('commodities', count($commoditiesId) > 0 ? $commoditiesId : ['none']);
    }
    /**
     * @inheritDoc
     */
    protected function applyAttributesFilter(
        QueryBuilder    $queryBuilder,
        array           $filter,
        array           $attributesParameters
    ): void {

    }
}
