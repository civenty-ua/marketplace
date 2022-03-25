<?php
declare(strict_types=1);
namespace App\Repository;

use App\Entity\CategoryTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CategoryTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method CategoryTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method CategoryTranslation[]    findAll()
 * @method CategoryTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategoryTranslation::class);
    }
}
