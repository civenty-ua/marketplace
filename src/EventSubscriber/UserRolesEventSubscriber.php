<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use Doctrine\ORM\{
    EntityManagerInterface,
    QueryBuilder,
};
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Event\{
    Commodity\CommodityDeactivationEvent,
    User\UserReceivedRoleEvent,
    User\UserLostRoleEvent,
};
use App\Entity\{
    User,
    Market\Commodity,
};
/**
 * User roles any manipulations events handler.
 */
class UserRolesEventSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface      $entityManager;
    private EventDispatcherInterface    $eventDispatcher;

    public function __construct(
        EntityManagerInterface   $entityManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->entityManager    = $entityManager;
        $this->eventDispatcher  = $eventDispatcher;
    }
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            UserReceivedRoleEvent::class    => ['userReceivedRole'],
            UserLostRoleEvent::class        => ['userLostRole'],
        ];
    }
    /**
     * On user receiving role event handler.
     *
     * @param   UserReceivedRoleEvent $event    Event.
     *
     * @return  void
     */
    public function userReceivedRole(UserReceivedRoleEvent $event): void
    {

    }
    /**
     * On user loosing role event handler.
     *
     * @param   UserLostRoleEvent $event        Event.
     *
     * @return  void
     */
    public function userLostRole(UserLostRoleEvent $event): void
    {
        $filter = [
            'activity'  => [true, false],
            'active'    => true,
            'user'      => $event->getUser(),
        ];

        switch ($event->getRole()) {
            case User::ROLE_SALESMAN:
                $filter['commodityType'] = Commodity::TYPE_PRODUCT;
                break;
            case User::ROLE_SERVICE_PROVIDER:
                $filter['commodityType'] = Commodity::TYPE_SERVICE;
                break;
            default:
                return;
        }

        /**
         * @var QueryBuilder    $queryBuilder
         * @var Commodity[]     $commoditiesToDeactivate
         */
        $queryBuilder               = $this
            ->entityManager
            ->getRepository(Commodity::class)
            ->listFilter(null, null, $filter);
        $commoditiesToDeactivate    = $queryBuilder->getQuery()->getResult();

        foreach ($commoditiesToDeactivate as $commodity) {
            $event = new CommodityDeactivationEvent($event->getUser(), $commodity);
            $this->eventDispatcher->dispatch($event);
        }
    }
}
