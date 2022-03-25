<?php
declare(strict_types=1);
namespace App\Repository;

use App\Entity\LessonModuleTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LessonModuleTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method LessonModuleTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method LessonModuleTranslation[]    findAll()
 * @method LessonModuleTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LessonModuleTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LessonModuleTranslation::class);
    }
}
