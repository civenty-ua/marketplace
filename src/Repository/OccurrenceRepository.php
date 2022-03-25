<?php
declare(strict_types=1);

namespace App\Repository;

use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\Traits\{
    FilterApplierTrait,
    CountPerCreatedDateTrait,
};
use App\Entity\{
    Category,
    Occurrence,
};

/**
 * @method Occurrence|null find($id, $lockMode = null, $lockVersion = null)
 * @method Occurrence|null findOneBy(array $criteria, array $orderBy = null)
 * @method Occurrence[]    findAll()
 * @method Occurrence[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OccurrenceRepository extends ServiceEntityRepository
{
    use FilterApplierTrait;
    use CountPerCreatedDateTrait;

    private int $maxCount;

    public function __construct(ManagerRegistry $registry)
    {
        $this->maxCount = 20;
        parent::__construct($registry, Occurrence::class);
    }

    public function findOneBySlug($value): ?Occurrence
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.slug = :val')
            ->andWhere('o.isActive = true')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getTopOccurrences(?int $id = null)
    {
        $q = $this->createQueryBuilder('i')
            ->andWhere('i.top = true')
            ->andWhere('i.isActive = true');
        if ($id) $q->andWhere('i.id != :id')
            ->setParameter('id',$id);
        $q->orderBy('i.id', 'DESC')
            ->setMaxResults($this->maxCount);


        return $q->getQuery()->getResult();
    }

    public function findOccurrences(?Category $category): array
    {
        $queryBuilder = $this
            ->createQueryBuilder('o')
            ->andWhere('o.isActive = true');

        if ($category) {
            $queryBuilder
                ->andWhere('o.category = :category')
                ->setParameter('category', $category);
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    /**
     * Get items count per time.
     *
     * @param DateTime $from Date from.
     * @param DateTime $to Date to.
     * @param array $filter Filter.
     *
     * @return  array                       Data, where
     *                                      key is date and
     *                                      value is items count for that day.
     */
    public function getCountPerTime(DateTime $from, DateTime $to, array $filter = []): array
    {
        return $this->getItemsCountPerCreatedDate($from, $to, $filter);
    }
}
