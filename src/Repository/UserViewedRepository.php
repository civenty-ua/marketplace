<?php

namespace App\Repository;

use App\Entity\Course;
use App\Entity\UserViewed;
use App\Entity\Webinar;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method UserViewed|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserViewed|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserViewed[]    findAll()
 * @method UserViewed[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserViewedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserViewed::class);
    }

    public function getViewedWebinarsByUser(UserInterface $user)
    {
        return $this->getViewedItemsByUser($user, Webinar::class);
    }

    public function getViewedCoursesByUser(UserInterface $user)
    {
        return $this->getViewedItemsByUser($user, Course::class);
    }

    private function getViewedItemsByUser(UserInterface $user, string $itemType = null)
    {
        $queryBuilder = $this->createQueryBuilder('uv');

        $queryBuilder
            ->where('uv.user = :user')
            ->setParameter('user', $user)
        ;

        if ($itemType) {
            $queryBuilder
                ->leftJoin('uv.item', 'i')
                ->andWhere($queryBuilder->expr()->isInstanceOf('i', $itemType))
            ;
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }
}
