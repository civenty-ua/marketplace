<?php
declare(strict_types = 1);

namespace App\Repository\Market;

use Doctrine\ORM\QueryBuilder;
use App\Repository\Helper\AliasMap;
use App\Repository\Traits\{
    FilterApplierTrait,
    Market\CommodityHelperTrait,
};
use App\Repository\Market\Exception\{
    CommoditiesEmptyFilterException,
    QueryEmptyResultException,
};
use App\Entity\{
    User,
    Market\Attribute,
    Market\Category,
    Market\CategoryAttributeListValue,
    Market\CategoryAttributeParameters,
    Market\CommodityAttributeValue,
};
/**
 * Commodity (products and services) repository trait.
 */
trait ProductsAndServicesRepositoryTrait
{
    use CommodityHelperTrait;
    use FilterApplierTrait;
    use CommodityRepositoryCommonInterfaceTrait {
        CommodityRepositoryCommonInterfaceTrait::applyAdditionalJoinsAndSelects as basicApplyAdditionalJoinsAndSelects;
        CommodityRepositoryCommonInterfaceTrait::applyBasicFilter               as basicApplyBasicFilter;
    }
    /**
     * List filter provider (for kits list filter).
     *
     * @param   array                           $filter                 Filter.
     * @param   array                           $filterAttributes       Filter (attributes).
     * @param   CategoryAttributeParameters[]   $attributesParameters   Category attributes parameters.
     *
     * @return  int[]                                                   Products ID set.
     * @throws  QueryEmptyResultException                               Filter success,
     *                                                                  but no commodities were found.
     * @throws  CommoditiesEmptyFilterException                         No filters actions were fired.
     */
    public function listFilterKits(
        array   $filter,
        array   $filterAttributes,
        array   $attributesParameters
    ): array {
        $repositoryEntity   = $this->getEntityName();
        $aliasMap           = (new AliasMap())
            ->setAlias($repositoryEntity, 'rootCommodity');
        $queryBuilder       = $this
            ->createQueryBuilder($aliasMap->getAlias($repositoryEntity))
            ->addSelect($aliasMap->getAlias($repositoryEntity));

        $this->applyAdditionalJoinsAndSelects($queryBuilder, $aliasMap);

        try {
            $this->applyBasicFilter($queryBuilder, $aliasMap, $filter);
            $filterExist = true;
        } catch (CommoditiesEmptyFilterException $exception) {
            $filterExist = false;
        }

        try {
            $this->applyAttributesFilter(
                $queryBuilder,
                $filterAttributes,
                $attributesParameters
            );
            $attributesFilterExist = true;
        } catch (CommoditiesEmptyFilterException $exception) {
            $attributesFilterExist = false;
        }

        if (!$filterExist && !$attributesFilterExist) {
            throw new CommoditiesEmptyFilterException();
        }

        $result = $this->queryIdSet($queryBuilder);

        if (count($result) === 0) {
            throw new QueryEmptyResultException();
        }

        return $result;
    }
    /**
     * @inheritDoc
     */
    protected function applyAdditionalJoinsAndSelects(
        QueryBuilder    $queryBuilder,
        AliasMap        $aliasMap
    ): void {
        $this->basicApplyAdditionalJoinsAndSelects($queryBuilder, $aliasMap);

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $aliasMap
            ->setAlias(Category::class, 'commodityCategory')
            ->setAlias(CommodityAttributeValue::class, 'attributeValues')
            ->setAlias(CategoryAttributeParameters::class, 'attributeParameters')
            ->setAlias(Attribute::class, 'commodityAttribute')
            ->setAlias(CategoryAttributeListValue::class, 'attributeListValue');

        $queryBuilder
            ->leftJoin("$rootAlias.category", $aliasMap->getAlias(Category::class))
            ->addSelect($aliasMap->getAlias(Category::class))
            ->leftJoin(
                "$rootAlias.commodityAttributesValues",
                $aliasMap->getAlias(CommodityAttributeValue::class)
            )
            ->addSelect($aliasMap->getAlias(CommodityAttributeValue::class));
//            ->leftJoin(
//                "{$aliasMap->getAlias(Category::class)}.categoryAttributesParameters",
//                $aliasMap->getAlias(CategoryAttributeParameters::class)
//            )
//            ->addSelect($aliasMap->getAlias(CategoryAttributeParameters::class))
//            ->leftJoin(
//                "{$aliasMap->getAlias(CategoryAttributeParameters::class)}.attribute",
//                $aliasMap->getAlias(Attribute::class)
//            )
//            ->addSelect($aliasMap->getAlias(Attribute::class))
//            ->leftJoin(
//                "{$aliasMap->getAlias(CategoryAttributeParameters::class)}.categoryAttributeListValues",
//                $aliasMap->getAlias(CategoryAttributeListValue::class)
//            )
//            ->addSelect($aliasMap->getAlias(CategoryAttributeListValue::class));
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
                $this->getCommodityType(),
                $aliasMap
            );
        } else {
            $this->applyCommoditiesActivityFilter(
                $queryBuilder,
                $this->getCommodityType(),
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
            $this->basicApplyBasicFilter($queryBuilder, $aliasMap, $filter);
            $anyFilterExist = true;
        } catch (CommoditiesEmptyFilterException $exception) {
            $anyFilterExist = false;
        }

        $alias  = $queryBuilder->getRootAliases()[0];
        $search = trim($filter['search'] ?? '');

        if (strlen($search) > 0) {
            $queryBuilder
                ->andWhere(
                    "$alias.title LIKE :searchString    OR ".
                    "$alias.id = :searchId              OR ".
                    "{$aliasMap->getAlias(Category::class)}.title LIKE :searchString"
                )
                ->setParameter('searchString', "%$search%")
                ->setParameter('searchId', (int) $search);

            $anyFilterExist = true;
        }
        if (isset($filter['category'])) {
            $this->applyFilter($queryBuilder, $alias, [
                'category' => $filter['category'],
            ]);
            $anyFilterExist = true;
        }
        if (isset($filter['userRegion'])) {
            $queryBuilder
                ->andWhere(
                    $queryBuilder->expr()->eq(
                        "{$aliasMap->getAlias(User::class)}.region",
                        ':userRegion'
                    )
                )
                ->setParameter('userRegion', $filter['userRegion']);
            $anyFilterExist = true;
        }

        if (!$anyFilterExist) {
            throw new CommoditiesEmptyFilterException();
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
        $alias = $queryBuilder->getRootAliases()[0];

        try {
            $commoditiesId = $this
                ->getEntityManager()
                ->getRepository(CommodityAttributeValue::class)
                ->getCommoditiesIdByAttributesFilter($filter, $attributesParameters);

            $this->applyFilter($queryBuilder, $alias, [
                'id' => $commoditiesId,
            ]);
        } catch (QueryEmptyResultException $exception) {
            $this->applyFilter($queryBuilder, $alias, [
                'id' => 'none',
            ]);
        }
    }
    /**
     * Get use commodity type.
     *
     * @return string                       Commodity type.
     */
    abstract protected function getCommodityType(): string;
}
