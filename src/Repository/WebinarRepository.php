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
    Webinar,
};

/**
 * @method Webinar|null find($id, $lockMode = null, $lockVersion = null)
 * @method Webinar|null findOneBy(array $criteria, array $orderBy = null)
 * @method Webinar[]    findAll()
 * @method Webinar[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WebinarRepository extends ServiceEntityRepository
{
    use FilterApplierTrait;
    use CountPerCreatedDateTrait;

    private int $maxCount;

    public function __construct(ManagerRegistry $registry)
    {
        $this->maxCount = 20;
        parent::__construct($registry, Webinar::class);
    }

    public function findOneBySlug($value): ?Webinar
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.slug = :val')
            ->andWhere('w.isActive = true')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult();

    }

    public function getTopWebinars(?int $id = null)
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

    public function findWebinars(?Category $category = null): array
    {
        $queryBuilder = $this
            ->createQueryBuilder('w')
            ->andWhere('w.isActive = true');

        if ($category) {
            $queryBuilder
                ->andWhere('w.category = :category')
                ->setParameter('category', $category);
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    public function activeWebinarsCount()
    {
        return $this
            ->createQueryBuilder('w')
            ->select('COUNT(w.id)')
            ->andWhere('w.isActive = true')
            ->getQuery()
            ->getSingleScalarResult();
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
