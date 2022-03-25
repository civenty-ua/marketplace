<?php
declare(strict_types = 1);

namespace App\Repository\Market;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Repository\Traits\FilterApplierTrait;
use App\Entity\Market\Category;
/**
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    use FilterApplierTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }
    /**
     * "findBy" custom version.
     *
     * Make sub-queries of all nested entities.
     *
     * @param   array       $criteria       Filter.
     * @param   array       $orderBy        Order.
     * @param   int|null    $limit          Limit.
     * @param   int|null    $offset         Offset.
     *
     * @return  Category[]                  Categories set.
     */
    public function findByCustom(
        array   $criteria,
        array   $orderBy    = [],
        ?int    $limit      = null,
        ?int    $offset     = null
    ): array {
        $aliasCategory              = 'category';
        $aliasAttribute             = 'attribute';
        $aliasAttributeParameters   = 'attributeParameters';
        $aliasAttributeListValues   = 'attributeListValues';
        $queryBuilder               = $this
            ->createQueryBuilder($aliasCategory)
            ->leftJoin("$aliasCategory.categoryAttributesParameters", $aliasAttributeParameters)
            ->leftJoin("$aliasAttributeParameters.attribute", $aliasAttribute)
            ->leftJoin("$aliasAttributeParameters.categoryAttributeListValues", $aliasAttributeListValues)
            ->addSelect($aliasCategory)
            ->addSelect($aliasAttribute)
            ->addSelect($aliasAttributeParameters)
            ->addSelect($aliasAttributeListValues);

        $this->applyFilter($queryBuilder, $aliasCategory, $criteria);

        foreach ($orderBy as $key => $value) {
            $queryBuilder->addOrderBy("$aliasCategory.$key", $value);
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
