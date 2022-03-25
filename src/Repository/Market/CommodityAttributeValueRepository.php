<?php
declare(strict_types = 1);

namespace App\Repository\Market;

use Throwable;
use InvalidArgumentException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Repository\Market\Exception\{
    CommoditiesEmptyFilterException,
    QueryEmptyResultException,
};
use App\Entity\Market\{
    Attribute,
    Category,
    CategoryAttributeParameters,
    Commodity,
    CommodityProduct,
    CommodityService,
    CommodityAttributeValue,
};
/**
 * @method CommodityAttributeValue|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommodityAttributeValue|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommodityAttributeValue[]    findAll()
 * @method CommodityAttributeValue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommodityAttributeValueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommodityAttributeValue::class);
    }
    /**
     * Get commodities ID set using attributes filter.
     *
     * @param   array                           $filter                 Attributes filter.
     * @param   CategoryAttributeParameters[]   $attributesParameters   Category attributes parameters.
     *
     * @return  array                                                   Commodities ID set.
     * @throws  QueryEmptyResultException                               Filter success,
     *                                                                  but no commodities were found.
     * @throws  CommoditiesEmptyFilterException                         No filters actions were fired.
     */
    public function getCommoditiesIdByAttributesFilter(array $filter, array $attributesParameters): array
    {
        $aliasValues            = 'attributeValues';
        $aliasCommodity         = 'commodity';
        $queryBuilder           = $this
            ->createQueryBuilder($aliasValues)
            ->leftJoin("$aliasValues.commodity", $aliasCommodity)
            ->select([
                "$aliasCommodity.id",
                "COUNT($aliasValues.id) as matching",
            ])
            ->groupBy("$aliasCommodity.id");
        $appliedFiltersCount    = 0;

        foreach ($attributesParameters as $attributeParameters) {
            try {
                switch ($attributeParameters->getAttribute()->getType()) {
                    case Attribute::TYPE_LIST:
                        $this->applyAttributeFilterList(
                            $queryBuilder,
                            $attributeParameters,
                            (array) ($filter[$attributeParameters->getId()] ?? [])
                        );
                        break;
                    case Attribute::TYPE_LIST_MULTIPLE:
                        $this->applyAttributeFilterListMultiple(
                            $queryBuilder,
                            $attributeParameters,
                            (array) ($filter[$attributeParameters->getId()] ?? [])
                        );
                        break;
                    case Attribute::TYPE_DICTIONARY:
                        $this->applyAttributeFilterDictionary(
                            $queryBuilder,
                            $attributeParameters,
                            (int) ($filter[$attributeParameters->getId()] ?? 0)
                        );
                        break;
                    case Attribute::TYPE_INT:
                        $this->applyAttributeFilterNumeric(
                            $queryBuilder,
                            $attributeParameters,
                            (array) ($filter[$attributeParameters->getId()] ?? [])
                        );
                        break;
                    default:
                        continue 2;
                }

                $appliedFiltersCount++;
            } catch (InvalidArgumentException $exception) {

            }
        }

        if ($appliedFiltersCount === 0) {
            throw new CommoditiesEmptyFilterException();
        }

        $queryResult = $queryBuilder
            ->having("matching = $appliedFiltersCount")
            ->getQuery()
            ->getResult();

        if (count($queryResult) === 0) {
            throw new QueryEmptyResultException();
        }

        return array_map(function(array $item): int {
            return $item['id'];
        }, $queryResult);
    }
    /**
     * Find and get attribute max value (for numeric attributes ONLY!) among specific category.
     *
     * @param   Category    $category       Category.
     * @param   Attribute   $attribute      Attribute.
     *
     * @return  float                       Max value.
     */
    public function getMaxIntegerValue(Category $category, Attribute $attribute): float
    {
        $alias              = 'attributesValues';
        $aliasCommodities   = 'commodity';

        switch ($category->getCommodityType()) {
            case Commodity::TYPE_PRODUCT:
                $commodityEntity = CommodityProduct::class;
                break;
            case Commodity::TYPE_SERVICE:
                $commodityEntity = CommodityService::class;
                break;
            default:
                return 0;
        }

        try {
            return (float) $this
                ->createQueryBuilder($alias)
                ->leftJoin(
                    $commodityEntity,
                    $aliasCommodities,
                    Join::WITH,
                    "$aliasCommodities.id = $alias.commodity"
                )
                ->andWhere("$aliasCommodities.category = :categoryId")
                ->andWhere("$alias.attribute = :attributeId")
                ->setParameter('categoryId', $category->getId())
                ->setParameter('attributeId', $attribute->getId())
                ->select("$alias.value")
                ->orderBy("CAST($alias.value as unsigned)", 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleResult()['value'] ?? 0;
        } catch (Throwable $exception) {
            return 0;
        }
    }
    /**
     * Apply attribute filter (type list).
     *
     * @param   QueryBuilder                $queryBuilder           Query builder.
     * @param   CategoryAttributeParameters $attributeParameters    Attribute parameters.
     * @param   array                       $filter                 Income filter.
     *
     * @return  void
     * @throws  InvalidArgumentException                            Filter is unsuitable.
     */
    private function applyAttributeFilterList(
        QueryBuilder                $queryBuilder,
        CategoryAttributeParameters $attributeParameters,
        array                       $filter
    ): void {
        $alias              = $queryBuilder->getRootAliases()[0];
        $attributeParameter = "attribute_{$attributeParameters->getId()}";
        $valueParameter     = "value_{$attributeParameters->getId()}";
        $values             = [];

        foreach ($filter as $value) {
            $valueInt = (int) $value;
            if ($valueInt > 0) {
                $values[] = $valueInt;
            }
        }

        if (count($values) === 0) {
            throw new InvalidArgumentException();
        }

        $queryBuilder
            ->orWhere("$alias.attribute = :$attributeParameter AND $alias.value IN (:$valueParameter)")
            ->setParameter($attributeParameter, $attributeParameters->getAttribute()->getId())
            ->setParameter($valueParameter,     $values);
    }
    /**
     * Apply attribute filter (type list multiple).
     *
     * @param   QueryBuilder                $queryBuilder           Query builder.
     * @param   CategoryAttributeParameters $attributeParameters    Attribute parameters.
     * @param   array                       $filter                 Income filter.
     *
     * @return  void
     * @throws  InvalidArgumentException                            Filter is unsuitable.
     */
    private function applyAttributeFilterListMultiple(
        QueryBuilder                $queryBuilder,
        CategoryAttributeParameters $attributeParameters,
        array                       $filter
    ): void {
        $alias              = $queryBuilder->getRootAliases()[0];
        $attributeParameter = "attribute_{$attributeParameters->getId()}";
        $subQueryStrings    = [];
        $subQueryParameters = [];

        foreach ($filter as $index => $value) {
            $valueInt = (int) $value;

            if ($valueInt <= 0) {
                continue;
            }

            $valueParameter                         = "value_{$index}_{$attributeParameters->getId()}";
            $subQueryStrings[]                      = "$alias.value LIKE :$valueParameter";
            $subQueryParameters[$valueParameter]    = "%$valueInt%";
        }

        if (count($subQueryStrings) === 0) {
            throw new InvalidArgumentException();
        }

        $subQueryString = implode(' OR ', $subQueryStrings);
        $queryBuilder
            ->orWhere("$alias.attribute = :$attributeParameter AND ($subQueryString)")
            ->setParameter($attributeParameter, $attributeParameters->getAttribute()->getId());

        foreach ($subQueryParameters as $key => $value) {
            $queryBuilder->setParameter($key, $value);
        }
    }
    /**
     * Apply attribute filter (type dictionary).
     *
     * @param   QueryBuilder                $queryBuilder           Query builder.
     * @param   CategoryAttributeParameters $attributeParameters    Attribute parameters.
     * @param   int                         $filter                 Income filter.
     *
     * @return  void
     * @throws  InvalidArgumentException                            Filter is unsuitable.
     */
    private function applyAttributeFilterDictionary(
        QueryBuilder                $queryBuilder,
        CategoryAttributeParameters $attributeParameters,
        int                         $filter
    ): void {
        $alias              = $queryBuilder->getRootAliases()[0];
        $attributeParameter = "attribute_{$attributeParameters->getId()}";
        $valueParameter     = "value_{$attributeParameters->getId()}";

        if ($filter <= 0) {
            throw new InvalidArgumentException();
        }

        $queryBuilder
            ->orWhere("$alias.attribute = :$attributeParameter AND $alias.value = :$valueParameter")
            ->setParameter($attributeParameter, $attributeParameters->getAttribute()->getId())
            ->setParameter($valueParameter,     $filter);
    }
    /**
     * Apply attribute filter (type number).
     *
     * @param   QueryBuilder                $queryBuilder           Query builder.
     * @param   CategoryAttributeParameters $attributeParameters    Attribute parameters.
     * @param   array                       $filter                 Income filter.
     *
     * @return  void
     * @throws  InvalidArgumentException                            Filter is unsuitable.
     */
    private function applyAttributeFilterNumeric(
        QueryBuilder                $queryBuilder,
        CategoryAttributeParameters $attributeParameters,
        array                       $filter
    ): void {
        $alias              = $queryBuilder->getRootAliases()[0];
        $valueFrom          = (int) ($filter[0] ?? 0);
        $valueTo            = (int) ($filter[1] ?? 0);
        $attributeParameter = "attribute_{$attributeParameters->getId()}";
        $valueParameterFrom = "value_{$attributeParameters->getId()}_from";
        $valueParameterTo   = "value_{$attributeParameters->getId()}_to";
        $queryString        = "$alias.attribute = :$attributeParameter";
        $queryParameters    = [
            $attributeParameter => $attributeParameters->getAttribute()->getId(),
        ];

        if ($valueFrom <= 0 && $valueTo<= 0) {
            throw new InvalidArgumentException();
        }

        if ($valueFrom > 0) {
            $queryString = "$queryString AND $alias.value >= :$valueParameterFrom";
            $queryParameters[$valueParameterFrom] = $valueFrom;
        }
        if ($valueTo > 0) {
            $queryString = "$queryString AND $alias.value <= :$valueParameterTo";
            $queryParameters[$valueParameterTo] = $valueTo;
        }

        $queryBuilder->orWhere($queryString);
        foreach ($queryParameters as $key => $value) {
            $queryBuilder->setParameter($key, $value);
        }
    }
}
