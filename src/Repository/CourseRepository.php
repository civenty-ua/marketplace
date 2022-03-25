<?php
declare(strict_types=1);

namespace App\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Entity\{
    Category,
    Course,
};

/**
 * @method Course|null find($id, $lockMode = null, $lockVersion = null)
 * @method Course|null findOneBy(array $criteria, array $orderBy = null)
 * @method Course[]    findAll()
 * @method Course[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CourseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Course::class);
    }

    public function findCourses(?Category $category = null): array
    {
        $queryBuilder = $this
            ->createQueryBuilder('c')
            ->andWhere('c.isActive = true');

        if ($category) {
            $queryBuilder
                ->andWhere('c.category = :category')
                ->setParameter('category', $category);
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    public function activeCoursesCount()
    {
        return $this
            ->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.isActive = true')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getLastUpdatedCourseByCategory(?Category $category): ?Course
    {
        $queryBuilder = $this
            ->createQueryBuilder('c')
            ->andWhere('c.isActive = true');

        if ($category) {
            $queryBuilder
                ->andWhere('c.category = :category')
                ->setParameter('category', $category);
        }

        $queryBuilder->setMaxResults(1);

        return $queryBuilder
            ->getQuery()
            ->getOneOrNullResult();
    }
}
