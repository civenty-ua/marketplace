<?php

namespace App\Repository\Market;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Entity\Market\UserFavorite;
/**
 * @method UserFavorite|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserFavorite|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserFavorite[]    findAll()
 * @method UserFavorite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserFavoriteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserFavorite::class);
    }
}
