<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\{
    Persistence\ManagerRegistry,
    ORM\NoResultException,
    ORM\NonUniqueResultException,
};

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    // /**
    //  * @return Comment[] Returns an array of Comment objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Comment
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    /**
     * Get items count.
     *
     * @param   array $filter               Filter.
     *
     * @return  int                         Total items count.
     */
    public function getCount(array $filter): int
    {
        $alias          = 'comment';
        $queryBuilder   = $this->createQueryBuilder($alias);

        if (count($filter) > 0) {
            $filterStringParts = [];

            foreach ($filter as $key => $value) {
                $filterStringParts[] = "$alias.$key = :$key";
                $queryBuilder->setParameter($key, $value);
            }

            $queryBuilder->where(implode(' AND ', $filterStringParts));
        }

        try {
            return (int) $queryBuilder
                ->select("count($alias.id)")
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException | NonUniqueResultException $exception) {
            return 0;
        }
    }
}
