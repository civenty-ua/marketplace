<?php

namespace App\Repository;

use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\{
    NonUniqueResultException,
    NoResultException,
};
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\Traits\{
    FilterApplierTrait,
    CountPerCreatedDateTrait,
};
use App\Entity\{Course, ItemRegistration, Webinar};
/**
 * @method ItemRegistration|null find($id, $lockMode = null, $lockVersion = null)
 * @method ItemRegistration|null findOneBy(array $criteria, array $orderBy = null)
 * @method ItemRegistration[]    findAll()
 * @method ItemRegistration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemRegistrationRepository extends ServiceEntityRepository
{
    use FilterApplierTrait;
    use CountPerCreatedDateTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ItemRegistration::class);
    }

    public function countAllUsersRegisteredToWebinars()
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT count(i.id)
            FROM App\Entity\ItemRegistration i,
            App\Entity\Webinar w
            WHERE i.itemId=w.id'
        );

        return $query->getSingleScalarResult();
    }

    /**
     * @param Course $course
     * @return int|mixed|string
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getCountUserInCourse(Course $course)
    {
        $qb = $this->createQueryBuilder('i');
        return $qb->select('count(i.id)')
            ->andWhere('i.itemId = :val')
            ->setParameter('val', $course)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param Webinar $webinar
     * @return int|mixed|string
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getCountUserInWebinar(Webinar $webinar)
    {
        $qb = $this->createQueryBuilder('i');
        return $qb->select('count(i.id)')
            ->andWhere('i.itemId = :val')
            ->setParameter('val', $webinar)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get items count per time.
     *
     * @param   DateTime    $from           Date from.
     * @param   DateTime    $to             Date to.
     * @param   array       $filter         Filter.
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
