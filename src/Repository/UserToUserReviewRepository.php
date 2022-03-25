<?php

namespace App\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Entity\UserToUserReview;
/**
 * @method UserToUserReview|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserToUserReview|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserToUserReview[]    findAll()
 * @method UserToUserReview[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserToUserReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserToUserReview::class);
    }

}
