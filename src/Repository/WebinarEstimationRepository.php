<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

use Doctrine\Persistence\ManagerRegistry;
use App\Entity\WebinarEstimation;
/**
 * @method WebinarEstimation|null find($id, $lockMode = null, $lockVersion = null)
 * @method WebinarEstimation|null findOneBy(array $criteria, array $orderBy = null)
 * @method WebinarEstimation[]    findAll()
 * @method WebinarEstimation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WebinarEstimationRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WebinarEstimation::class);
    }

}
