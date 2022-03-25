<?php

namespace App\Repository\Market;

use App\Entity\Market\UserCommodityNotification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserCommodityNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserCommodityNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserCommodityNotification[]    findAll()
 * @method UserCommodityNotification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserCommodityNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserCommodityNotification::class);
    }
}