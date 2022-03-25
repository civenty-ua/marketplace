<?php
declare(strict_types = 1);

namespace App\Repository\Market;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Repository\Traits\FilterApplierTrait;
use App\Entity\Market\CategoryAttributeParameters;
/**
 * @method CategoryAttributeParameters|null find($id, $lockMode = null, $lockVersion = null)
 * @method CategoryAttributeParameters|null findOneBy(array $criteria, array $orderBy = null)
 * @method CategoryAttributeParameters[]    findAll()
 * @method CategoryAttributeParameters[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryAttributeParametersRepository extends ServiceEntityRepository
{
    use FilterApplierTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategoryAttributeParameters::class);
    }
    /**
     * "findBy" custom version.
     *
     * Make sub-queries of all nested entities.
     *
     * @param   array       $criteria           Filter.
     * @param   array       $orderBy            Order.
     * @param   int|null    $limit              Limit.
     * @param   int|null    $offset             Offset.
     *
     * @return  CategoryAttributeParameters[]   Parameters set.
     */
    public function findByCustom(
        array   $criteria,
        array   $orderBy    = [],
        ?int    $limit      = null,
        ?int    $offset     = null
    ): array {
        $aliases        = [
            'attributeParameters'   => 'attributeParameters',
            'attributeListValues'   => 'attributeListValues',
            'category'              => 'category',
            'attribute'             => 'attribute',
        ];
        $queryBuilder   = $this
            ->createQueryBuilder($aliases['attributeParameters'])
            ->leftJoin("{$aliases['attributeParameters']}.categoryAttributeListValues", $aliases['attributeListValues'])
            ->leftJoin("{$aliases['attributeParameters']}.category", $aliases['category'])
            ->leftJoin("{$aliases['attributeParameters']}.attribute", $aliases['attribute'])
            ->addSelect($aliases['attributeParameters'])
            ->addSelect($aliases['attributeListValues'])
            ->addSelect($aliases['category'])
            ->addSelect($aliases['attribute']);

        $this->applyFilter($queryBuilder, $aliases['attributeParameters'], $criteria);

        foreach ($orderBy as $key => $value) {
            $queryBuilder->addOrderBy("{$aliases['attributeParameters']}.$key", $value);
        }

        if ($limit) {
            $queryBuilder->setMaxResults($limit);
        }
        if ($offset) {
            $queryBuilder->setFirstResult($offset);
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
