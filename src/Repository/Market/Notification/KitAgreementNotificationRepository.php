<?php
declare(strict_types=1);

namespace App\Repository\Market\Notification;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Entity\Market\Notification\KitAgreementNotification;
/**
 * @method KitAgreementNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method KitAgreementNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method KitAgreementNotification[]    findAll()
 * @method KitAgreementNotification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class KitAgreementNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, KitAgreementNotification::class);
    }
}
