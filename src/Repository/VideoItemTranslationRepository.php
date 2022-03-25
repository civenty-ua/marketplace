<?php
declare(strict_types=1);
namespace App\Repository;

use App\Entity\VideoItemTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VideoItemTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method VideoItemTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method VideoItemTranslation[]    findAll()
 * @method VideoItemTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VideoItemTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VideoItemTranslation::class);
    }
}
