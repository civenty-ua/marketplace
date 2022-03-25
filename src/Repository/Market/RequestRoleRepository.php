<?php

namespace App\Repository\Market;

use App\Entity\Market\RequestRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RequestRole|null find($id, $lockMode = null, $lockVersion = null)
 * @method RequestRole|null findOneBy(array $criteria, array $orderBy = null)
 * @method RequestRole[]    findAll()
 * @method RequestRole[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RequestRoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RequestRole::class);
    }

    /**
     * @return int|mixed|string
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCountNewRole()
    {
        return $this->createQueryBuilder('r')
            ->select("count(r.id)")
            ->orWhere('r.isApproved = false')
            ->orWhere('r.isApproved is null')
            ->andWhere('r.isActive = true')
            ->getQuery()
            ->getSingleScalarResult();
    }


    /**
     * @param $user
     * @param $role
     * @return int|mixed|string|null
     */
    public function getCurrentRequestRole($user, $role)
    {
        try {
            $query =  $this->createQueryBuilder('r');
            return $query
                ->andWhere('r.user = :user')
                ->andWhere("r.role = :role")
                ->andWhere(
                    $query->expr()->orX(
                        $query->expr()->eq('r.isApproved', 0),
                        $query->expr()->isNull('r.isApproved')
                    )
                )
                ->setParameters(['user' => $user, 'role' => $role])
                ->getQuery()
                ->getOneOrNullResult();
        } catch (\Exception $e) {
            return null;
        }
    }
}
