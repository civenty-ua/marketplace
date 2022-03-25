<?php

namespace App\Repository;

use App\Entity\CompanyMessages;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CompanyMessages|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompanyMessages|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompanyMessages[]    findAll()
 * @method CompanyMessages[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyMessagesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompanyMessages::class);
    }

    // /**
    //  * @return CompanyMessages[] Returns an array of CompanyMessages objects
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
    public function findOneBySomeField($value): ?CompanyMessages
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
