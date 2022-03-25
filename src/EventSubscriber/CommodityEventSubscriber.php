<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\Commodity\CommodityRequestEvent;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use UnexpectedValueException;
use App\Entity\Market\CommodityProduct;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Event\Commodity\CommodityKitDeactivationByCoAuthorEvent;
use App\Service\Market\CommodityActivity\{
    CommodityActivityManager,
    ActivityChangeAccessDeniedException,
    KitApproveAccessDeniedException,
    KitApprovedButDeactivatedException,
    KitNotApprovedException,
};
use App\Entity\Market\Notification\BidOffer;
use App\Entity\Market\Notification\KitAgreementNotification;
use App\Entity\Market\Notification\Notification;
use App\Entity\Market\Notification\PriceOfferNotification;
use App\Entity\UserToUserReview;
use App\Event\Commodity\CommodityKitApprovingEvent;
use App\Event\Commodity\CommodityOfferReviewSendEvent;
use App\Service\Notification\KitAgreementNotificationSender;
use App\Service\Notification\OfferReviewNotificationSender;
use App\Service\Notification\SystemNotificationSender;
use DateTime;
use App\Entity\Market\Commodity;
use App\Entity\Market\CommodityKit;
use App\Entity\Market\UserCommodityNotification;
use App\Entity\User;
use App\Event\Commodity\Admin\CommodityAdminCreateEvent;
use App\Event\Commodity\Admin\CommodityAdminActivationEvent;
use App\Event\Commodity\Admin\CommodityAdminDeactivationEvent;
use App\Event\Commodity\Admin\CommodityAdminUpdateEvent;
use App\Event\Commodity\CommodityActiveToExpireEvent;
use App\Event\Commodity\CommodityCreateEvent;
use App\Event\Commodity\CommodityActivationEvent;
use App\Event\Commodity\CommodityDeactivationEvent;
use App\Event\Commodity\CommodityUpdateEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use function array_merge;

class CommodityEventSubscriber implements EventSubscriberInterface
{
    private SessionInterface $sessionInterface;
    private EntityManagerInterface $entityManager;
    private EventDispatcherInterface $eventDispatcher;
    private KitAgreementNotificationSender $kitAgreementNotificationSender;
    private OfferReviewNotificationSender $offerReviewNotificationSender;
    private TranslatorInterface $translator;
    private SystemNotificationSender $systemNotificationSender;
    private CommodityActivityManager $commodityActivityManager;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        EntityManagerInterface         $entityManager,
        EventDispatcherInterface       $eventDispatcher,
        KitAgreementNotificationSender $kitAgreementNotificationSender,
        OfferReviewNotificationSender  $offerReviewNotificationSender,
        TranslatorInterface            $translator,
        SystemNotificationSender       $systemNotificationSender,
        CommodityActivityManager       $commodityActivityManager,
        TokenStorageInterface          $tokenStorage,
        SessionInterface               $sessionInterface
    ) {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->kitAgreementNotificationSender = $kitAgreementNotificationSender;
        $this->offerReviewNotificationSender = $offerReviewNotificationSender;
        $this->translator = $translator;
        $this->systemNotificationSender = $systemNotificationSender;
        $this->commodityActivityManager = $commodityActivityManager;
        $this->tokenStorage = $tokenStorage;
        $this->sessionInterface = $sessionInterface;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CommodityCreateEvent::class => ['commodityCreate'],
            CommodityAdminCreateEvent::class => ['commodityAdminCreate'],
            CommodityUpdateEvent::class => ['commodityUpdate'],
            CommodityAdminUpdateEvent::class => ['commodityAdminUpdate'],
            CommodityActivationEvent::class => ['commodityActivation'],
            CommodityAdminActivationEvent::class => ['commodityAdminActivation'],
            CommodityDeactivationEvent::class => ['commodityDeactivation'],
            CommodityAdminDeactivationEvent::class => ['commodityAdminDeactivation'],
            CommodityActiveToExpireEvent::class => ['commodityActiveToExpire'],
            CommodityOfferReviewSendEvent::class => ['commodityOfferReviewSend'],
            CommodityKitApprovingEvent::class => ['commodityKitApproving'],
            CommodityKitDeactivationByCoAuthorEvent::class => ['commodityKitLeaving'],
            CommodityRequestEvent::class => ['commodityRequest'],
        ];
    }
    /**
     * Commodity request event handler.
     *
     * @param   CommodityRequestEvent $event     Event.
     *
     * @return  void
     */
    public function commodityRequest(CommodityRequestEvent $event): void
    {
        $sessionKey         = 'commoditiesRequested';
        $userVisitedPages   = (array) $this->sessionInterface->get($sessionKey, []);
        $commodityId        = $event->getCommodity()->getId();

        if (!isset($userVisitedPages[$commodityId])) {
            $event->getCommodity()->increaseViewsAmount();
            $this->entityManager->flush();

            $userVisitedPages[$commodityId] = true;
            $this->sessionInterface->set($sessionKey, $userVisitedPages);
        }
    }

    /**
     * On commodity create event handler.
     *
     * @param   CommodityCreateEvent $event
     *
     * @return  void
     * @throws  ActivityChangeAccessDeniedException
     * @throws  KitApproveAccessDeniedException
     */
    public function commodityCreate(CommodityCreateEvent $event): void
    {
        $commodity = $event->getCommodity();

        $this->commodityActivityManager->activateCommodity($commodity, $this->getCurrentUser());

        if ($commodity->getCommodityType() === Commodity::TYPE_KIT) {
            /** @var CommodityKit $commodity */
            try {
                $this->commodityActivityManager->checkKitCanBeApproved($commodity, $this->getCurrentUser());
                $this->commodityActivityManager->approveKit($commodity, $this->getCurrentUser());
            } catch (KitNotApprovedException $exception) {
                $this->runKitAgreementProcess($commodity);
            } catch (KitApprovedButDeactivatedException $exception) {

            }
        }
    }
    /**
     * On commodity create (from admin panel) event handler.
     *
     * @param   CommodityAdminCreateEvent $event
     *
     * @return  void
     */
    public function commodityAdminCreate(CommodityAdminCreateEvent $event)
    {
        //TODO
    }
    /**
     * On commodity update event handler.
     *
     * @param   CommodityUpdateEvent $event
     *
     * @return  void
     * @throws  KitApproveAccessDeniedException
     */
    public function commodityUpdate(CommodityUpdateEvent $event)
    {
        $commodity = $event->getCommodity();

        if ($commodity->getCommodityType() === Commodity::TYPE_KIT) {
            /** @var CommodityKit $commodity */
            $this->clearKitPreviousAgreements($commodity);

            try {
                $this->commodityActivityManager->checkKitCanBeApproved($commodity, $this->getCurrentUser());
                $this->commodityActivityManager->approveKit($commodity, $this->getCurrentUser());
            } catch (KitNotApprovedException $exception) {
                $this->runKitReAgreementProcess($commodity);
            } catch (KitApprovedButDeactivatedException $exception) {

            }
        }
    }
    /**
     * On commodity update (from admin panel) event handler.
     *
     * @param   CommodityAdminUpdateEvent $event
     *
     * @return  void
     */
    public function commodityAdminUpdate(CommodityAdminUpdateEvent $event)
    {

    }
    /**
     * On commodity activation event handler.
     *
     * @param   CommodityActivationEvent $event
     *
     * @return  void
     * @throws  ActivityChangeAccessDeniedException
     */
    public function commodityActivation(CommodityActivationEvent $event)
    {
        $this->commodityActivityManager->activateCommodity($event->getCommodity(), $this->getCurrentUser());
    }
    /**
     * On commodity activation (from admin panel) event handler.
     *
     * @param   CommodityAdminActivationEvent $event
     *
     * @return  void
     * @throws  ActivityChangeAccessDeniedException
     */
    public function commodityAdminActivation(CommodityAdminActivationEvent $event)
    {
        $this->commodityActivityManager->activateCommodity($event->getCommodity(), $this->getCurrentUser());
    }
    /**
     * On commodity deactivation event handler.
     *
     * @param   CommodityDeactivationEvent $event
     *
     * @return  void
     * @throws  ActivityChangeAccessDeniedException
     */
    public function commodityDeactivation(CommodityDeactivationEvent $event)
    {
        $this->commodityActivityManager->deactivateCommodity($event->getCommodity(), $event->getUser());
    }
    /**
     * On commodity deactivation (from admin panel) event handler.
     *
     * @param   CommodityAdminDeactivationEvent $event
     *
     * @return  void
     * @throws  ActivityChangeAccessDeniedException
     */
    public function commodityAdminDeactivation(CommodityAdminDeactivationEvent $event)
    {
        $this->commodityActivityManager->deactivateCommodity($event->getCommodity(), $this->getCurrentUser());
    }
    /**
     * On commodity deactivation (by date expiring) event handler.
     *
     * @param   CommodityActiveToExpireEvent $event
     *
     * @return  void
     * @throws  ActivityChangeAccessDeniedException
     */
    public function commodityActiveToExpire(CommodityActiveToExpireEvent $event)
    {
        $commodity = $event->getCommodity();

        $this->commodityActivityManager->deactivateCommodity($commodity, $this->getAdmin());

        $this->createSingleUserCommodityNotification($commodity, get_class($event));

        if ($commodity->getCommodityType() === Commodity::TYPE_KIT) {
            /** @var CommodityKit $commodity */
            foreach ($commodity->getCoAuthors(false) as $coAuthor) {
                $this->createSingleUserCommodityNotification($commodity, get_class($event), $coAuthor);
            }
        } else {
            $kits = $this->entityManager
                ->getRepository(CommodityKit::class)
                ->getActiveKitsThatHasExpiredCommodities($event->getCommodity());

            foreach ($kits as $kit) {
                $commodityActiveToExpireEvent = new CommodityActiveToExpireEvent();
                $commodityActiveToExpireEvent->setCommodity($kit);
                $this->eventDispatcher->dispatch($commodityActiveToExpireEvent);
            }
        }
    }
    /**
     * On kit approving event handler.
     *
     * @param   CommodityKitApprovingEvent $event
     *
     * @return  void
     * @throws  KitApproveAccessDeniedException
     */
    public function commodityKitApproving(CommodityKitApprovingEvent $event)
    {
        $kitAgreement   = $event->getNotification();
        $kit            = $kitAgreement->getCommodity();

        if ($kitAgreement->getStatus() !== KitAgreementNotification::STATUS_PENDING) {
            return;
        }

        $kitAgreement->setStatus(KitAgreementNotification::STATUS_APPROVED);
        $this->entityManager->flush();

        /** @var KitAgreementNotification[] $kitNotifications */
        $kitNotifications = $this
            ->entityManager
            ->getRepository(KitAgreementNotification::class)
            ->findBy([
                'status' => [
                    KitAgreementNotification::STATUS_PENDING,
                    KitAgreementNotification::STATUS_APPROVED,
                ],
                'commodity' => $kit,
            ]);

        foreach ($kitNotifications as $kitNotification) {
            if ($kitNotification->getStatus() !== KitAgreementNotification::STATUS_APPROVED) {
                return;
            }
        }

        try {
            $this->commodityActivityManager->approveKit($kit, $this->getCurrentUser());
            $this
                ->systemNotificationSender
                ->sendSystemNotificationsToKitAgreementParticipators(
                    $kitNotifications,
                    $kit->getTitle()
                );
        } catch (KitApprovedButDeactivatedException $exception) {
            $this
                ->systemNotificationSender
                ->kitApprovedButDeactivated($kitNotifications, $kit->getTitle());
        }
    }
    /**
     * On commodity offer review sending event handler.
     *
     * @param CommodityOfferReviewSendEvent $event
     *
     * @return void
     */
    public function commodityOfferReviewSend(CommodityOfferReviewSendEvent $event)
    {
        $data = $this->buildDataForOfferReviewByNotificationType($event->getNotification());
        $this->offerReviewNotificationSender->sendSingleNotification($data);
        $event->getNotification()->setOfferReviewNotificationSent(true);
        $this->entityManager->flush();
    }
    /**
     * Try to activate commodity.
     *
     * @param   CommodityKitDeactivationByCoAuthorEvent $event
     *
     * @return  void
     * @throws  ActivityChangeAccessDeniedException
     */
    public function commodityKitLeaving(CommodityKitDeactivationByCoAuthorEvent $event)
    {
        $this->commodityActivityManager->leftKit($event->getCommodity(), $this->getCurrentUser());

        $coauthors = $this->buildReceiversOfKitDependingOnAdminAction($event->getCommodity(),
            false,
            true,
            $event->getDeactivationInitiator()
        );
        $this->sendSystemNotificationForKitAuthorByAuthority(
            $event->getCommodity()->getUser(),
            $this->buildMessagePartsForCommodityKitDeactivationByCoAuthorEvent($event),
            'author'
        );

        foreach ($coauthors as $coauthor) {
            $this->sendSystemNotificationForKitAuthorByAuthority(
                $coauthor,
                $this->buildMessagePartsForCommodityKitDeactivationByCoAuthorEvent($event),
                'coauthor'
            );
        }
    }
    /**
     * @param CommodityKit $kit
     *
     * @return  void
     */
    private function runKitAgreementProcess(CommodityKit $kit): void
    {
        foreach ($kit->getCoAuthors(false) as $coAuthor) {
            $this->kitAgreementNotificationSender->sendSingleNotification([
                'sender' => $kit->getUser(),
                'receiver' => $coAuthor,
                'title' => 'Заявка на створення спільної пропозиції ' . $kit->getTitle(),
                'message' => "Шановний $coAuthor, користувач {$kit->getUser()} пропонує вам стати співучасником спільної пропозиції
                {$kit->getTitle()}. У вас є 3 дні з моменту надходження цього повідомлення, щоб підтвердити участь.",
                'phone' => $kit->getUser()->getPhone(),
                'name' => $kit->getUser()->getName(),
                'commodity' => $kit
            ]);
        }
    }

    private function clearKitPreviousAgreements(CommodityKit $kit): void
    {
        /** @var KitAgreementNotification[] $notifications */
        $notifications = $this
            ->entityManager
            ->getRepository(KitAgreementNotification::class)
            ->findBy([
                'status'    => [
                    KitAgreementNotification::STATUS_PENDING,
                    KitAgreementNotification::STATUS_APPROVED,
                ],
                'commodity' => $kit,
            ]);

        foreach ($notifications as $notification) {
            $notification->setStatus(KitAgreementNotification::STATUS_UPDATED_BY_OWNER);
            $notification->setUpdatedAt(new DateTime());
        }

        $this->entityManager->flush();
    }

    private function runKitReAgreementProcess(CommodityKit $kit): void
    {
        $kit->setIsApproved(false);
        $kit->setUpdatedAt(new DateTime());//todo implement logic according to Snapshot pattern into Kit
        $this->entityManager->flush();

        $this->sendKitDeactivationSystemNotificationsToKitCoauthors($kit, false);
        $this->sendNewKitAgreementNotificationsForParticipants($kit, false);
    }

    private function buildMessagePartsForCommodityKitDeactivationByCoAuthorEvent(CommodityKitDeactivationByCoAuthorEvent $event): array
    {
        return [
            '%kit%' => $event->getCommodity()->getTitle(),
            '%coauthor%' => $event->getDeactivationInitiator(),
            '%user%' => $event->getCommodity()->getUser()
        ];
    }

    private function sendSystemNotificationForKitAuthorByAuthority(User $authorOrCoAuthor, array $msgParts, string $authorityType)
    {
        $this->systemNotificationSender->sendSingleNotification([
            'title' => $this->translator->trans('cron.notification_sender.msg_parts_by_event.CommodityKitDeactivationByCoAuthorEvent.' .
                $authorityType . '.title', $msgParts),
            'message' => $this->translator->trans('cron.notification_sender.msg_parts_by_event.CommodityKitDeactivationByCoAuthorEvent.' .
                $authorityType . '.message', $msgParts),
            'receiver' => $authorOrCoAuthor,
        ]);
    }

    private function buildDataForOfferReviewByNotificationType(Notification $notification): array
    {
        $dataParts = $this->getDataPartsByNotificationType($notification);
        $data['title'] = $dataParts['title'];
        $data['message'] = $dataParts['message'];
        $data['parentNotification'] = $notification;
        $data['userToUserReview'] = $this->createSingleUserToUserOffer($notification);
        //отправляет инициатору покупки или продаже о владельце или покупателе при бид и пра оффере
        $data['receiver'] = array_key_exists('receiver', $dataParts)
            ? $dataParts['receiver']
            : $notification->getSender();
        $data['sender'] = array_key_exists('sender', $dataParts)
            ? $dataParts['sender']
            : $notification->getReceiver();

        return $data;
    }

    private function getDataPartsByNotificationType(Notification $notification): array
    {
        $mainDomainParts = $messagesDomainParts = [
            'cron',
            'notification_sender',
            'msg_parts_by_event',
            'CommodityOfferReviewSendEvent',
        ];
        $data = [];

        switch (get_class($notification)) {
            case BidOffer::class:
                $messagesDomainParts[] = $notification->getCommodity()->getCommodityType();

                if ($notification->getCommodity()->getCommodityType() === Commodity::TYPE_PRODUCT) {
                    /** @var CommodityProduct $product */
                    $product                = $notification->getCommodity();
                    $messagesDomainParts[]  = $product->getType();
                }
                break;
            case KitAgreementNotification::class:
                $messagesDomainParts[]  = Commodity::TYPE_KIT;
                $data['sender']         = $notification->getSender();
                $data['receiver']       = $notification->getReceiver();
                break;
            case PriceOfferNotification::class:
                $messagesDomainParts[]  = 'price';
                break;
            default:
                throw new InvalidArgumentException(
                    "caught notification with incorrect type, id: {$notification->getId()}"
                );
        }

        $mainDomain      = implode('.', $mainDomainParts);
        $messagesDomain  = implode('.', $messagesDomainParts);
        $data['message'] = $this->translator->trans("$messagesDomain.message", [
            '%user%'        => $notification->getReceiver(),
            '%msgPart%'     => $this->translator->trans("$messagesDomain.msgPart"),
            '%product%'     => $notification->getCommodity()->getTitle() ?? '',
            '%end_msg%'     => $this->translator->trans("$mainDomain.end_msg", [
                '%user%'        => $notification->getReceiver(),
            ]),
        ]);
        $data['title']   = $this->translator->trans("$mainDomain.title", [
            '%product%'     => $notification->getCommodity()->getTitle() ?? '',
            '%titlePart%'   => $this->translator->trans("$messagesDomain.titlePart"),
        ]);
        return $data;
    }

    private function createSingleUserToUserOffer(Notification $notification): UserToUserReview
    {
        $userToUserReview = new UserToUserReview();
        $userToUserReview->setUser($notification->getSender());
        $userToUserReview->setTargetUser($notification->getReceiver());
        $userToUserReview->setCreatedAt(new \DateTime());
        $userToUserReview->setUpdatedAt(new \DateTime());
        $this->entityManager->persist($userToUserReview);
        $this->entityManager->flush();

        return $userToUserReview;
    }

    private function createSingleUserCommodityNotification(Commodity $commodity, string $eventType, User $user = null)
    {
        $userCommodityNotification = new UserCommodityNotification();
        $user != null
            ? $userCommodityNotification->setUser($user)
            : $userCommodityNotification->setUser($commodity->getUser());
        $userCommodityNotification->setCommodity($commodity);
        $userCommodityNotification->setCreatedAt(new  \DateTime());
        $userCommodityNotification->setUpdatedAt(new  \DateTime());
        $userCommodityNotification->setEventType($eventType);
        $userCommodityNotification->setNotificationSent(false);
        $this->entityManager->persist($userCommodityNotification);
        $this->entityManager->flush();
    }

    private function sendKitDeactivationSystemNotificationsToKitCoauthors(CommodityKit $commodity, bool $byAdmin)
    {
        $coAuthors = $this->buildReceiversOfKitDependingOnAdminAction($commodity, $byAdmin,true);

        foreach ($coAuthors as $coAuthor) {
            $this->systemNotificationSender->sendSingleNotification(array_merge([
                'receiver' => $coAuthor
            ], $this->buildNotificationTitleAndMessageForKit($commodity, 'systemMessage', $byAdmin)));
        }
    }

    private function sendNewKitAgreementNotificationsForParticipants(CommodityKit $commodity, bool $byAdmin)
    {
        $authorAndCoauthors = $this->buildReceiversOfKitDependingOnAdminAction($commodity, $byAdmin,false);

        foreach ($authorAndCoauthors as $coAuthor) {
            $this->kitAgreementNotificationSender->sendSingleNotification(array_merge([
                'name' => $commodity->getUser(),
                'receiver' => $coAuthor,
                'commodity' => $commodity,
                'sender' => $commodity->getUser(),
            ], $this->buildNotificationTitleAndMessageForKit($commodity, 'kitAgreement', $byAdmin)));
        };
    }

    private function buildReceiversOfKitDependingOnAdminAction(CommodityKit $commodity, bool $byAdmin, bool $commoditiesSnapshot,?User $deactivationInitiator = null): array
    {
        if ($byAdmin === true) {
            $coAuthors = $commodity->getCoAuthors($commoditiesSnapshot);
            $coAuthors[] = $commodity->getUser();
        } else {
            $coAuthors = $commodity->getCoAuthors($commoditiesSnapshot);
        }

        if ($deactivationInitiator) {
            $coAuthors = $this->deleteDeactivationInitiatorFromRecievers($coAuthors, $deactivationInitiator);
        }

        return $coAuthors;
    }

    private function buildNotificationTitleAndMessageForKit(CommodityKit $commodity, string $notificationType, bool $byAdmin): array
    {
        return [
            'title' => $this->translator->trans('cron.notification_sender.msg_parts_by_event.CommodityUpdateEvent.kit.' . $notificationType . '.title', [
                '%kit%' => $commodity->getTitle(),
                '%byAdmin%' => $byAdmin ? 'адміном' : '',
            ]),
            'message' => $this->translator->trans('cron.notification_sender.msg_parts_by_event.CommodityUpdateEvent.kit.' . $notificationType . '.message', [
                '%user%' => $byAdmin ? $commodity->getUser() : 'Адмін UHBDP',
                '%kit%' => $commodity->getTitle(),
            ]),
        ];
    }

    private function deleteDeactivationInitiatorFromRecievers(array $coAuthors, User $deactivationInitiator): array
    {
        foreach ($coAuthors as $key => $coAuthor) {
            if ($coAuthor === $deactivationInitiator) {
                unset($coAuthors[$key]);
            }
        }
        return $coAuthors;
    }
    /**
     * Try to find and get admin.
     *
     * @return User                         Admin.
     * @throws UnexpectedValueException     No admin were found.
     */
    private function getAdmin(): User
    {
        /** @var user|null $admin */
        $admin = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy([
                'id' => 1,
            ]);

        if (!$admin || !in_array(User::ROLE_SUPER_ADMIN, $admin->getRoles())) {
            throw new UnexpectedValueException('user with ID 1 is not an amin, or completely were not found');
        }

        return $admin;
    }
    /**
     * Get current user, if exists.
     *
     * @return User|null
     */
    private function getCurrentUser(): ?User
    {
        $user = $this->tokenStorage->getToken()
            ? $this->tokenStorage->getToken()->getUser()
            : null;

        return $user instanceof User ? $user : null;
    }
}
