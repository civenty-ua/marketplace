<?php

namespace App\Repository\Market;

use App\Entity\Market\LegalCompanyType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LegalCompanyType|null find($id, $lockMode = null, $lockVersion = null)
 * @method LegalCompanyType|null findOneBy(array $criteria, array $orderBy = null)
 * @method LegalCompanyType[]    findAll()
 * @method LegalCompanyType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LegalCompanyTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LegalCompanyType::class);
    }

    // /**
    //  * @return LegalCompanyType[] Returns an array of LegalCompanyType objects
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
    public function findOneBySomeField($value): ?LegalCompanyType
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
