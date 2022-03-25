<?php

namespace App\Repository\Market;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Entity\Market\CategoryAttributeListValue;
/**
 * @method CategoryAttributeListValue|null find($id, $lockMode = null, $lockVersion = null)
 * @method CategoryAttributeListValue|null findOneBy(array $criteria, array $orderBy = null)
 * @method CategoryAttributeListValue[]    findAll()
 * @method CategoryAttributeListValue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryAttributeListValueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategoryAttributeListValue::class);
    }
}
