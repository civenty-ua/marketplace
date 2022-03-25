<?php
declare(strict_types=1);

namespace App\Repository;


use App\Entity\Other;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;
/**
 * @method Other|null find($id, $lockMode = null, $lockVersion = null)
 * @method Other|null findOneBy(array $criteria, array $orderBy = null)
 * @method Other[]    findAll()
 * @method Other[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OtherRepository  extends ServiceEntityRepository
{

    private int $maxCount;

    public function __construct(ManagerRegistry $registry)
    {
        $this->maxCount = 20;
        parent::__construct($registry, Other::class);
    }


    /**
     * @return int|mixed|string
     */
    public function getTopOthers()
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.top = true')
            ->andWhere('i.isActive = true')
            ->orderBy('i.id', 'DESC')
            ->setMaxResults($this->maxCount)
            ->getQuery()
            ->getResult();
    }
}