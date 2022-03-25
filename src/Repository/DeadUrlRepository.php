<?php

namespace App\Repository;

use App\Entity\DeadUrl;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DeadUrl|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeadUrl|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeadUrl[]    findAll()
 * @method DeadUrl[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeadUrlRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeadUrl::class);
    }

    // /**
    //  * @return DeadUrl[] Returns an array of DeadUrl objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DeadUrl
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
