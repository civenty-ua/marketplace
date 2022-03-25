<?php
declare(strict_types=1);
namespace App\Repository;

use App\Entity\LessonTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LessonTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method LessonTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method LessonTranslation[]    findAll()
 * @method LessonTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LessonTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LessonTranslation::class);
    }
}
