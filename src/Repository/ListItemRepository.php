<?php

namespace App\Repository;

use App\Entity\ListItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ListItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListItem[]    findAll()
 * @method ListItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListItem::class);
    }

    // /**
    //  * @return ListItem[] Returns an array of ListItem objects
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
    public function findOneBySomeField($value): ?ListItem
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
