<?php

namespace App\Repository;

use App\Entity\CoursePartSort;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CoursePartSort|null find($id, $lockMode = null, $lockVersion = null)
 * @method CoursePartSort|null findOneBy(array $criteria, array $orderBy = null)
 * @method CoursePartSort[]    findAll()
 * @method CoursePartSort[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CoursePartSortRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoursePartSort::class);
    }

    // /**
    //  * @return CoursePartSort[] Returns an array of CoursePartSort objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CoursePartSort
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
