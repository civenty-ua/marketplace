<?php

namespace App\Repository;

use App\Entity\VideoItemWatching;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VideoItemWatching|null find($id, $lockMode = null, $lockVersion = null)
 * @method VideoItemWatching|null findOneBy(array $criteria, array $orderBy = null)
 * @method VideoItemWatching[]    findAll()
 * @method VideoItemWatching[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VideoItemWatchingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VideoItemWatching::class);
    }

    // /**
    //  * @return VideoItemWatching[] Returns an array of VideoItemWatching objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?VideoItemWatching
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
