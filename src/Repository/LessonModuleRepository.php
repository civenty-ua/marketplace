<?php

namespace App\Repository;

use App\Entity\LessonModule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LessonModule|null find($id, $lockMode = null, $lockVersion = null)
 * @method LessonModule|null findOneBy(array $criteria, array $orderBy = null)
 * @method LessonModule[]    findAll()
 * @method LessonModule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LessonModuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LessonModule::class);
    }

    // /**
    //  * @return LessonModule[] Returns an array of LessonModule objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LessonModule
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
