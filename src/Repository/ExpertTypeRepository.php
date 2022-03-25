<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\ExpertType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExpertType|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExpertType|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExpertType[]    findAll()
 * @method ExpertType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExpertTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExpertType::class);
    }
}
