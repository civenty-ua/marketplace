<?php

namespace App\Repository;

use App\Entity\FeedbackFormQuestionTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FeedbackFormQuestionTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method FeedbackFormQuestionTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method FeedbackFormQuestionTranslation[]    findAll()
 * @method FeedbackFormQuestionTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FeedbackFormQuestionTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FeedbackFormQuestionTranslation::class);
    }

    // /**
    //  * @return FeedbackFormQuestionTranslation[] Returns an array of FeedbackFormQuestionTranslation objects
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
    public function findOneBySomeField($value): ?FeedbackFormQuestionTranslation
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
