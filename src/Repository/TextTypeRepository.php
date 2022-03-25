<?php

namespace App\Repository;

use App\Entity\TextType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TextType|null find($id, $lockMode = null, $lockVersion = null)
 * @method TextType|null findOneBy(array $criteria, array $orderBy = null)
 * @method TextType[]    findAll()
 * @method TextType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TextTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TextType::class);
    }

    // /**
    //  * @return TextType[] Returns an array of TextType objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TextType
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
