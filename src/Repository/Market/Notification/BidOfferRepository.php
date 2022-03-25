<?php

namespace App\Repository\Market\Notification;

use App\Entity\Market\Notification\BidOffer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BidOffer|null find($id, $lockMode = null, $lockVersion = null)
 * @method BidOffer|null findOneBy(array $criteria, array $orderBy = null)
 * @method BidOffer[]    findAll()
 * @method BidOffer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BidOfferRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BidOffer::class);
    }

    // /**
    //  * @return BidOffer[] Returns an array of BidOffer objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?BidOffer
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
