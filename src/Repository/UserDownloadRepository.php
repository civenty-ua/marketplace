<?php

namespace App\Repository;

use App\Entity\UserDownload;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserDownload|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserDownload|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserDownload[]    findAll()
 * @method UserDownload[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserDownloadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserDownload::class);
    }

    // /**
    //  * @return UserDownload[] Returns an array of UserDownload objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserDownload
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
