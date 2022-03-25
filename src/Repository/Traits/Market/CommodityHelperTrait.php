<?php
declare(strict_types = 1);

namespace App\Repository\Traits\Market;

use DateTime;
use Doctrine\ORM\QueryBuilder;
use App\Repository\Traits\{
    IdSelectorTrait,
    FilterApplierTrait,
    UserHelperTrait,
};
use App\Repository\Helper\AliasMap;
use App\Entity\{
    User,
    Market\Commodity,
    Market\CommodityProduct,
    Market\CommodityService,
    Market\CommodityKit,
};
/**
 * Commodity repository helper trait.
 *
 * Provides helpful methods to apply filters in different ways.
 */
trait CommodityHelperTrait
{
    use IdSelectorTrait;
    use UserHelperTrait;
    use FilterApplierTrait;
    /**
     * Apply commodity activity filter.
     *
     * @param   QueryBuilder    $queryBuilder   Query builder.
     * @param   string          $commodityType  Commodity type.
     * @param   AliasMap        $aliasMap       Alias map.
     *
     * @return  void
     */
    protected function applyCommoditiesActivityFilter(
        QueryBuilder    $queryBuilder,
        string          $commodityType,
        AliasMap        $aliasMap
    ): void {
        $rootAlias          = $queryBuilder->getRootAliases()[0];
        $commodityFilters   = $this->getCommoditiesActivityFilterConditions($commodityType, $rootAlias);
        $userFilters        = $this->getMarketUserActivityFilterConditions(
            $aliasMap->getAlias(User::class),
            $this->getCommodityUserRequiredRoles($commodityType)
        );

        foreach (array_merge($commodityFilters, $userFilters) as $filter) {
            $queryBuilder->andWhere($filter['query']);
            foreach ($filter['parameters'] as $key => $value) {
                $queryBuilder->setParameter($key, $value);
            }
        }
    }
    /**
     * Apply commodity inactivity filter.
     *
     * @param   QueryBuilder    $queryBuilder   Query builder.
     * @param   string          $commodityType  Commodity type.
     * @param   AliasMap        $aliasMap       Alias map.
     *
     * @return  void
     */
    protected function applyCommoditiesInactivityFilter(
        QueryBuilder    $queryBuilder,
        string          $commodityType,
        AliasMap        $aliasMap
    ): void {
        $rootAlias          = $queryBuilder->getRootAliases()[0];
        $commodityFilters   = $this->getCommoditiesInactivityFilterConditions($commodityType, $rootAlias);
        $userFilters        = $this->getMarketUserInactivityFilterConditions(
            $aliasMap->getAlias(User::class),
            $this->getCommodityUserRequiredRoles($commodityType)
        );
        $queryParts         = [];

        foreach (array_merge($commodityFilters, $userFilters) as $filter) {
            $queryParts[] = $filter['query'];

            foreach ($filter['parameters'] as $key => $value) {
                $queryBuilder->setParameter($key, $value);
            }
        }

        $queryBuilder->andWhere(implode(' OR ', $queryParts));
    }
    /**
     * Get commodity activity filters set.
     *
     * @param   string          $commodityType  Commodity type.
     * @param   string          $alias          Alias.
     *
     * @return  array                           Filters set.
     */
    private function getCommoditiesActivityFilterConditions(string $commodityType, string $alias): array
    {
        $result = [
            [
                'query'         => "$alias.isActive = :isActive",
                'parameters'    => ['isActive' => 1],
            ],
            [
                'query'         => "$alias.activeFrom <= :activeFrom",
                'parameters'    => ['activeFrom' => new DateTime('now')],
            ],
            [
                'query'         => "$alias.activeTo >= :activeTo",
                'parameters'    => ['activeTo' => new DateTime('now')],
            ],
        ];

        if ($commodityType === Commodity::TYPE_KIT) {
            $kitsWithInactiveCommodities = $this->getKitsWithInactiveCommodities();

            $result[] = [
                'query'         => "$alias.isApproved = :isApproved",
                'parameters'    => ['isApproved' => 1],
            ];
            if (count($kitsWithInactiveCommodities) > 0) {
                $result[] = [
                    'query'         => "$alias.id NOT IN (:kitsWithInactiveCommodities)",
                    'parameters'    => [
                        'kitsWithInactiveCommodities' => $kitsWithInactiveCommodities,
                    ],
                ];
            }
        }

        return $result;
    }
    /**
     * Get commodity inactivity filters set.
     *
     * @param   string          $commodityType  Commodity type.
     * @param   string          $alias          Alias.
     *
     * @return  array                           Filters set.
     */
    private function getCommoditiesInactivityFilterConditions(string $commodityType, string $alias): array
    {
        $result = [
            [
                'query'         => "$alias.isActive = :isActive OR $alias.isActive IS NULL",
                'parameters'    => ['isActive' => 0],
            ],
            [
                'query'         => "$alias.activeFrom > :activeFrom",
                'parameters'    => ['activeFrom' => new DateTime('now')],
            ],
            [
                'query'         => "$alias.activeTo < :activeTo",
                'parameters'    => ['activeTo' => new DateTime('now')],
            ],
        ];

        if ($commodityType === Commodity::TYPE_KIT) {
            $kitsWithInactiveCommodities = $this->getKitsWithInactiveCommodities();

            $result[] = [
                'query'         => "$alias.isApproved = :isApproved OR $alias.isApproved IS NULL",
                'parameters'    => ['isApproved' => 0],
            ];
            if (count($kitsWithInactiveCommodities) > 0) {
                $result[] = [
                    'query'         => "$alias.id IN (:kitsWithInactiveCommodities)",
                    'parameters'    => [
                        'kitsWithInactiveCommodities' => $kitsWithInactiveCommodities,
                    ],
                ];
            }
        }

        return $result;
    }
    /**
     * Get kits ID set with inactive commodities.
     *
     * @return int[]                            Kits ID set.
     */
    private function getKitsWithInactiveCommodities(): array
    {
        $inactiveCommodities = [];

        foreach ([
            CommodityProduct::class,
            CommodityService::class,
        ] as $repository) {
            /** @var QueryBuilder $queryBuilder */
            $queryBuilder           = $this
                ->getEntityManager()
                ->getRepository($repository)
                ->listFilter(null, null, [
                    'activity' => false,
                ]);
            $inactiveCommodities    = array_merge(
                $inactiveCommodities,
                $this->queryIdSet($queryBuilder)
            );
        }

        $kitAlias           = 'kit';
        $commoditiesAlias   = 'kitCommodities';
        $queryBuilder       = $this
            ->createQueryBuilder($kitAlias)
            ->leftJoin("$kitAlias.commodities", $commoditiesAlias)
            ->where("$commoditiesAlias.id IN (:commodities)")
            ->setParameter('commodities', $inactiveCommodities);

        return $this->queryIdSet($queryBuilder);
    }
    /**
     * Get required roles set for given commodity type.
     *
     * @param   string $commodityType       Commodity type.
     *
     * @return  string[]                    Roles set.
     */
    private function getCommodityUserRequiredRoles(string $commodityType): array
    {
        switch ($commodityType) {
            case Commodity::TYPE_PRODUCT:
                return CommodityProduct::REQUIRED_USER_ROLES;
            case Commodity::TYPE_SERVICE:
                return CommodityService::REQUIRED_USER_ROLES;
            case Commodity::TYPE_KIT:
                return CommodityKit::REQUIRED_USER_ROLES;
            default:
                return [];
        }
    }
}
