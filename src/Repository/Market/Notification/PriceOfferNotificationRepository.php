<?php

namespace App\Repository\Market\Notification;

use App\Entity\Market\Notification\PriceOfferNotification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PriceOfferNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method PriceOfferNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method PriceOfferNotification[]    findAll()
 * @method PriceOfferNotification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PriceOfferNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PriceOfferNotification::class);
    }

    // /**
    //  * @return OfferReview[] Returns an array of OfferReview objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?OfferReview
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
