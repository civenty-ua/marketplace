<?php

namespace App\Repository;

use App\Entity\UserLastLessonViewed;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserLastLessonViewed|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserLastLessonViewed|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserLastLessonViewed[]    findAll()
 * @method UserLastLessonViewed[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserLastLessonViewedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserLastLessonViewed::class);
    }

}
