<?php

namespace App\Repository\Market;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Market\CommodityFavorite;
/**
 * @method CommodityFavorite|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommodityFavorite|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommodityFavorite[]    findAll()
 * @method CommodityFavorite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommodityFavoriteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommodityFavorite::class);
    }
}
