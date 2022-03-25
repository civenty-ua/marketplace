<?php

namespace App\Repository\Market;

use App\Entity\Market\KitAgreement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method KitAgreement|null find($id, $lockMode = null, $lockVersion = null)
 * @method KitAgreement|null findOneBy(array $criteria, array $orderBy = null)
 * @method KitAgreement[]    findAll()
 * @method KitAgreement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class KitAgreementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, KitAgreement::class);
    }

    // /**
    //  * @return KitAgreement[] Returns an array of KitAgreement objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('k.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?KitAgreement
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
