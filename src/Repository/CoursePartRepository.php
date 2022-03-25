<?php

namespace App\Repository;

use App\Entity\CoursePart;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CoursePart|null find($id, $lockMode = null, $lockVersion = null)
 * @method CoursePart|null findOneBy(array $criteria, array $orderBy = null)
 * @method CoursePart[]    findAll()
 * @method CoursePart[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CoursePartRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoursePart::class);
    }

}
