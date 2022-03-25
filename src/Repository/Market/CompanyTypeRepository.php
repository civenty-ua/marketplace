<?php

namespace App\Repository\Market;

use App\Entity\Market\CompanyType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CompanyType|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompanyType|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompanyType[]    findAll()
 * @method CompanyType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompanyType::class);
    }

    // /**
    //  * @return CompanyType[] Returns an array of CompanyType objects
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
    public function findOneBySomeField($value): ?CompanyType
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
