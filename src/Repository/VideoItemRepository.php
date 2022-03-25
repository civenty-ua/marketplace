<?php
declare(strict_types=1);
namespace App\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Entity\{
    Course,
    VideoItem,
};
/**
 * @method VideoItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method VideoItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method VideoItem[]    findAll()
 * @method VideoItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VideoItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VideoItem::class);
    }
}
