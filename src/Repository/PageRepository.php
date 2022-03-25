<?php

namespace App\Repository;

use App\Entity\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Page|null find($id, $lockMode = null, $lockVersion = null)
 * @method Page|null findOneBy(array $criteria, array $orderBy = null)
 * @method Page[]    findAll()
 * @method Page[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Page::class);
    }

    /**
     * @return Page[] Returns an array of Page objects
     */

    public function findPageByTypeName($typeCode)
    {
        return $this->createQueryBuilder('p')
            ->addSelect('pt')
            ->join('p.translations', 'pt')
            ->leftJoin('p.typePage', 'tp')
            ->andWhere('tp.code = :code')
            ->setParameter('code', $typeCode)
            ->getQuery()
            ->getResult();
    }

    public function findAllBySlug(string $slug)
    {
        return $this->createQueryBuilder('p')
            ->where('p.alias LIKE :slug')
            ->setParameter('slug', $slug . '%')
            ->getQuery()
            ->getResult();
    }
}
