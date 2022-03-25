<?php

namespace App\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Entity\UserToUserFeedback;
/**
 * @method UserToUserFeedback|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserToUserFeedback|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserToUserFeedback[]    findAll()
 * @method UserToUserFeedback[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserToUserFeedbackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserToUserFeedback::class);
    }
}
