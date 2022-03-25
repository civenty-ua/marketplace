<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\ExpertTypeTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExpertTypeTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExpertTypeTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExpertTypeTranslation[]    findAll()
 * @method ExpertTypeTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExpertTypeTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExpertTypeTranslation::class);
    }
}
