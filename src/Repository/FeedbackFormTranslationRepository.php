<?php

namespace App\Repository;

use App\Entity\FeedbackFormTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FeedbackFormTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method FeedbackFormTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method FeedbackFormTranslation[]    findAll()
 * @method FeedbackFormTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FeedbackFormTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FeedbackFormTranslation::class);
    }

    // /**
    //  * @return FeedbackFormTranslation[] Returns an array of FeedbackFormTranslation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FeedbackFormTranslation
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
