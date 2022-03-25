<?php

namespace App\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Entity\CourseUserProgress;
/**
 * @method CourseUserProgress|null find($id, $lockMode = null, $lockVersion = null)
 * @method CourseUserProgress|null findOneBy(array $criteria, array $orderBy = null)
 * @method CourseUserProgress[]    findAll()
 * @method CourseUserProgress[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CourseUserProgressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CourseUserProgress::class);
    }
}
