<?php

namespace App\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Entity\Region;
/**
 * @method Region|null find($id, $lockMode = null, $lockVersion = null)
 * @method Region|null findOneBy(array $criteria, array $orderBy = null)
 * @method Region[]    findAll()
 * @method Region[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RegionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Region::class);
    }

    public function getAllRegion() {
        $regions =  $this->createQueryBuilder('p')
            ->select(['p.id', 'pt.name'])
            ->join('p.translations', 'pt')
            ->andWhere("pt.locale = 'uk'")
            ->getQuery()
            ->getResult();
        $results = [];
        foreach ($regions as  $item) {
            $results[$item['name']] = $item;
        }
        return $results;
    }

    public function getAllRegionObject() {
        $regions =  $this->createQueryBuilder('p')
            ->join('p.translations', 'pt')
            ->andWhere("pt.locale = 'uk'")
            ->getQuery()
            ->getResult();
        $results = [];
        foreach ($regions as  $item) {
            $results[$item->getid()] = $item;
        }
        return $results;
    }
}
