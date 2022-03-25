<?php
declare(strict_types = 1);

namespace App\Repository\Market;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Repository\Market\Exception\CommoditiesEmptyFilterException;
use App\Repository\Helper\AliasMap;
use App\Entity\{
    Region,
    Market\Commodity,
    Market\CommodityProduct,
    Market\UserCertificate,
};
/**
 * @method CommodityProduct|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommodityProduct|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommodityProduct[]    findAll()
 * @method CommodityProduct[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommodityProductRepository extends ServiceEntityRepository
{
    use ProductsAndServicesRepositoryTrait {
        ProductsAndServicesRepositoryTrait::applyAdditionalJoinsAndSelects  as parentApplyAdditionalJoinsAndSelects;
        ProductsAndServicesRepositoryTrait::applyBasicFilter                as parentApplyBasicFilter;
    }

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommodityProduct::class);
    }
    /**
     * @inheritDoc
     */
    protected function getCommodityType(): string
    {
        return Commodity::TYPE_PRODUCT;
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
        $aliasMap->setAlias(Region::class, 'productRegion');

        $queryBuilder
            ->leftJoin("$rootAlias.region", $aliasMap->getAlias(Region::class))
            ->addSelect($aliasMap->getAlias(Region::class));
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
            $anyFilterExist = true;
        } catch (CommoditiesEmptyFilterException $exception) {
            $anyFilterExist = false;
        }

        $alias          = $queryBuilder->getRootAliases()[0];
        $organicOnly    = $filter['organicOnly'] ?? null;

        if (isset($filter['type'])) {
            $this->applyFilter($queryBuilder, $alias, [
                'type' => $filter['type'],
            ]);
            $anyFilterExist = true;
        }
        if (is_bool($organicOnly)) {
            $this->applyBasicFilterOrganic($queryBuilder, $aliasMap, $organicOnly);
            $anyFilterExist = true;
        }

        if (!$anyFilterExist) {
            throw new CommoditiesEmptyFilterException();
        }
    }
    /**
     * Apply products organic filters.
     *
     * @param   QueryBuilder    $queryBuilder   Query builder.
     * @param   AliasMap        $aliasMap       Alias map.
     * @param   bool            $value          Filter value.
     *
     * @return  void
     */
    private function applyBasicFilterOrganic(
        QueryBuilder    $queryBuilder,
        AliasMap        $aliasMap,
        bool            $value
    ): void {
        $alias              = $queryBuilder->getRootAliases()[0];
        $aliasCertificates  = $aliasMap->getAlias(UserCertificate::class);

        switch ($value) {
            case true;
                $queryBuilder
                    ->andWhere("$alias.isOrganic = 1")
                    ->andWhere("$aliasCertificates.isEcology = 1")
                    ->andWhere("$aliasCertificates.approved = 1");
                break;
            case false;
                $queryBuilder->andWhere("$alias.isOrganic = 0");
                break;
            default:
        }
    }
}
