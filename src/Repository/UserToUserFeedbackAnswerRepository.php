<?php

namespace App\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Entity\UserToUserFeedbackAnswer;
/**
 * @method UserToUserFeedbackAnswer|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserToUserFeedbackAnswer|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserToUserFeedbackAnswer[]    findAll()
 * @method UserToUserFeedbackAnswer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserToUserFeedbackAnswerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserToUserFeedbackAnswer::class);
    }

}
