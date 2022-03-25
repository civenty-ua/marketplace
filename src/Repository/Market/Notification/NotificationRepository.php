<?php

namespace App\Repository\Market\Notification;

use App\Entity\Market\Notification\BidOffer;
use App\Entity\Market\Notification\DealOffer;
use App\Entity\Market\Notification\KitAgreementNotification;
use App\Entity\Market\Notification\Notification;
use App\Entity\Market\Notification\OfferReview;
use App\Entity\Market\Notification\PriceOfferNotification;
use App\Entity\Market\Notification\SystemMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function getActiveUserNotifications(UserInterface $user)
    {
        $this->createQueryBuilder('n')
            ->andWhere('n.receiver = :user')
            ->setParameter('user', $user)
            ->getQuery();
    }

    public function countAllUserNotifications(UserInterface $user)
    {
        return $this->createQueryBuilder('n')
            ->select('count(n.id)')
            ->andWhere('n.receiver = :user')
            ->andWhere('n.isSoftDeleted = false')
            ->setParameter('user', $user)
            ->getQuery()->getSingleScalarResult();
    }

    public function countAllActiveUserNotification(UserInterface $user)
    {
        return $this->createQueryBuilder('n')
            ->select('count(n.id)')
            ->andWhere('n.isActive = true')
            ->andWhere('n.receiver = :user')
            ->andWhere('n.isSoftDeleted = false')
            ->setParameter('user', $user)
            ->getQuery()->getSingleScalarResult();
    }

    public function countAllTrashedUserNotification(UserInterface $user)
    {
        return $this->createQueryBuilder('n')
            ->select('count(n.id)')
            ->andWhere('n.isActive = false')
            ->andWhere('n.receiver = :user')
            ->andWhere('n.isSoftDeleted = false')
            ->setParameter('user', $user)
            ->getQuery()->getSingleScalarResult();
    }

    public function countAllUnreadUserNotification(UserInterface $user)
    {
        return $this->createQueryBuilder('n')
            ->select('count(n.id)')
            ->where('n.isRead = false')
            ->andWhere('n.receiver = :user')
            ->andWhere('n.isSoftDeleted = false')
            ->setParameter('user', $user)
            ->getQuery()->getSingleScalarResult();
    }

    public function getFilteredNotifications(?string $search, ?int $type, UserInterface $user, bool $isActive): Query
    {
        $query = $this->createQueryBuilder('n')
            ->andWhere('n.receiver = :user')
            ->setParameter('user', $user)
            ->andWhere('n.isActive = :isActive')
            ->andWhere('n.isSoftDeleted = false')
            ->setParameter('isActive', $isActive);

        if ($search) {
            $query
                ->andWhere('n.message LIKE :search or n.title LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        if ($type && $type >= 0 && $type < 8) {
            switch ($type) {
                case 2:
                    $query->andWhere('n.isRead = false');
                    break;
                case 3:
                    $query->andWhere($query->expr()->isInstanceOf('n', BidOffer::class));
                    break;
                case 4:
                    $query->andWhere($query->expr()->isInstanceOf('n', KitAgreementNotification::class));
                    break;
                case 5:
                    $query->andWhere($query->expr()->isInstanceOf('n', SystemMessage::class));
                    break;
                case 6:
                    $query->andWhere($query->expr()->isInstanceOf('n', OfferReview::class));
                    break;
                case 7:
                    $query->andWhere($query->expr()->isInstanceOf('n', PriceOfferNotification::class));
                    break;
            }
        }
        return $query
            ->orderBy('n.createdAt', 'DESC')
            ->addOrderBy('n.id', 'DESC')
            ->getQuery();
    }

    public function batchDeleteUserNotification(UserInterface $user, array $ids): void
    {
        $this->createQueryBuilder('n')->update()
            ->set('n.isActive', 'false')
            ->where('n.id IN (:ids)')
            ->andWhere('n.receiver = :user')
            ->setParameters([
                'ids' => $ids,
                'user' => $user
            ])
            ->getQuery()->execute();
    }

    public function batchRestoreUserNotification(UserInterface $user, array $ids): void
    {
        $this->createQueryBuilder('n')->update()
            ->set('n.isActive', 'true')
            ->where('n.id IN (:ids)')
            ->andWhere('n.receiver = :user')
            ->setParameters([
                'ids' => $ids,
                'user' => $user
            ])
            ->getQuery()->execute();
    }

    public function softDeleteUserNotification(UserInterface $user)
    {
        $this->createQueryBuilder('n')->update()
            ->set('n.isSoftDeleted', 'true')
            ->where('n.receiver = :user')
            ->andWhere('n.isActive = false')
            ->setParameter('user', $user)
            ->getQuery()->execute();
    }
}
