<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserToUserRate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserToUserRate|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserToUserRate|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserToUserRate[]    findAll()
 * @method UserToUserRate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserToUserRateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserToUserRate::class);
    }

    public function getUserRateValue(User $targetUser)
    {
       $rate =  $this->createQueryBuilder('utur')
            ->select('avg(utur.rate) as rating')
            ->where('utur.targetUser = :user')
            ->setParameter('user', $targetUser)
            ->getQuery()
            ->getSingleScalarResult();
        if ($rate){
            $rate = round($rate,1);
        }
        return $rate ?? 0;
    }

    public function getUserVotedValue(User $targetUser)
    {
       return $this->createQueryBuilder('utur')
            ->select('count(utur.id) as voted')
            ->where('utur.targetUser = :user')
            ->setParameter('user', $targetUser)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
