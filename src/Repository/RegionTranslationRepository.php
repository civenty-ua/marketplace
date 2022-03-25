<?php
declare(strict_types=1);
namespace App\Repository;

use App\Entity\RegionTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RegionTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method RegionTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method RegionTranslation[]    findAll()
 * @method RegionTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RegionTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RegionTranslation::class);
    }
}
