<?php
declare(strict_types=1);
namespace App\Repository;

use App\Entity\ExpertTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExpertTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExpertTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExpertTranslation[]    findAll()
 * @method ExpertTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExpertTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExpertTranslation::class);
    }

    // /**
    //  * @return ArticleTranslation[] Returns an array of ArticleTranslation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ArticleTranslation
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
