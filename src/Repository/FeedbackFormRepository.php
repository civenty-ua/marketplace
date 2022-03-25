<?php

namespace App\Repository;

use App\Entity\FeedbackForm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FeedbackForm|null find($id, $lockMode = null, $lockVersion = null)
 * @method FeedbackForm|null findOneBy(array $criteria, array $orderBy = null)
 * @method FeedbackForm[]    findAll()
 * @method FeedbackForm[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FeedbackFormRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FeedbackForm::class);
    }

    // /**
    //  * @return FeedbackForm[] Returns an array of FeedbackForm objects
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
    public function findOneBySomeField($value): ?FeedbackForm
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
