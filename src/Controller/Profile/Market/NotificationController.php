<?php
declare(strict_types = 1);

namespace App\Controller\Profile\Market;

use App\Event\Commodity\CommodityKitApprovingEvent;
use DateTime;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    JsonResponse,
};
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Liip\ImagineBundle\Exception\Config\Filter\NotFoundException;
use App\Form\Market\ProfileUserToUserReviewFormType;
use App\Entity\{
    User,
    UserToUserRate,
};
use App\Entity\Market\Notification\{
    Notification,
    OfferReview,
    KitAgreementNotification,
};
/**
 * @package App\Controller\Profile
 */
class NotificationController extends ProfileMarketController
{
    private const QUERY_PARAMETER_SEARCH = 'search';
    private const QUERY_PARAMETER_FILTER_TYPE = 'type';
    private const QUERY_PARAMETER_FILTER_TYPE_VALUES = [
        'Всі',
        'Непрочитані',
        'Покупка',
        'Спільні пропозиції',
        'Система',
        'Відгуки',
        'Пропозиція ціни'
    ];
    private const QUERY_PARAMETER_PAGE = 'page';
    /**
     * @Route("/profile/market/notifications", name="my_notifications")
     */
    public function listNotifications(Request $request, PaginatorInterface $paginator): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();
        $allCount = $this->notificationDataService->getUserNotificationCounters($user);
        $all = $request->query->all();
        $items = $this->notificationDataService->getFilteredNotifications($request, $user, $paginator, true);
        $pageValueIncome = (int)($requestData['page'] ?? 0);
        $page = $pageValueIncome > 0 ? $pageValueIncome : 1;

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'content' => $this->render('notification/notification-block.html.twig',
                    [
                        'items' => $items,
                        'currentPage' => $page,
                        'paginationName' => 'page',
                    ])->getContent(),
            ], Response::HTTP_OK);
        } else {
            return $this->render('profile_marketplace/my-notifications.html.twig', [
                'appliedQueryParams' => [
                    self::QUERY_PARAMETER_SEARCH => $this->notificationDataService->getSearch($all),
                    self::QUERY_PARAMETER_FILTER_TYPE => $this->notificationDataService->getTypeFilter($all),
                    self::QUERY_PARAMETER_PAGE => $this->notificationDataService->getPage($all),
                ],
                'filter' => [
                    self::QUERY_PARAMETER_SEARCH => $this->notificationDataService->getSearch($all),
                    'typeValues' => self::QUERY_PARAMETER_FILTER_TYPE_VALUES,
                ],
                'items' => $items,
                'currentPage' => $page,
                'paginationName' => 'page',
                'activeUserNotificationCount' => $allCount['activeUserNotificationCount'],
                'trashedUserNotificationCount' => $allCount['trashedUserNotificationCount'],
                'userFavoritesCount' => $user->getFavorites()->count(),
                'trashFlag' => false,
                'listAjaxUrl' => $this->generateUrl('my_notifications',
                    [
                        self::QUERY_PARAMETER_SEARCH => 'SEARCH_VALUE',
                        self::QUERY_PARAMETER_FILTER_TYPE => 'TYPE_VALUES',
                        self::QUERY_PARAMETER_PAGE => 'PAGE_VALUE',
                    ]),
            ]);
        }
    }

    /**
     * @Route("/profile/market/notification/{id}", name="notification_detail")
     * @throws NotFoundException
     */
    public function detailNotification(int $id): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();
        $notification = $this->getDoctrine()->getRepository(Notification::class)->find($id);
        $this->checkNotificationExistsAndBelongsCurrentUser($notification);
        $this->setIsReadIfUnreadedNotification($notification);
        $allCount = $this->notificationDataService->getUserNotificationCounters($user);

        return $this->render('profile_marketplace/my-notification-detail.html.twig', [
            'activeUserNotificationCount' => $allCount['activeUserNotificationCount'],
            'trashedUserNotificationCount' => $allCount['trashedUserNotificationCount'],
            'userFavoritesCount' => $user->getFavorites()->count(),
            'notification' => $notification,
            'rate' => $notification->getSender()
                ? $this->getDoctrine()->getRepository(UserToUserRate::class)
                    ->getUserRateValue($notification->getSender())
                : null
        ]);
    }

    /**
     * @Route("/profile/market/notification/review/{id}", name="notification_review_detail")
     * @throws NotFoundException
     */
    public function detailReviewNotification(Request $request, int $id): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();
        $notification = $this->getDoctrine()->getRepository(OfferReview::class)->find($id);
        $this->checkNotificationExistsAndBelongsCurrentUser($notification);
        $this->setIsReadIfUnreadedNotification($notification);
        $allCount = $this->notificationDataService->getUserNotificationCounters($user);
        $form = $this->createForm(ProfileUserToUserReviewFormType::class, $notification->getUserToUserReview());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->getErrors()->count() === 0) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($notification->getUserToUserReview());
            $notification->getParentNotification()->setOfferReviewNotificationSent(true);
            $this->systemNotificationSender->sendSingleNotification([
                'receiver' => $notification->getUserToUserReview()->getTargetUser(),
                'message' => "Шановний {$notification->getUserToUserReview()->getTargetUser()}, користувач {$user} залишив про вас відгук."
            ]);
            $entityManager->flush();

            return $this->redirectToRoute('my_notifications');
        }
        return $this->render('profile_marketplace/my-notification-review-detail.html.twig', [
            'activeUserNotificationCount' => $allCount['activeUserNotificationCount'],
            'trashedUserNotificationCount' => $allCount['trashedUserNotificationCount'],
            'userFavoritesCount' => $user->getFavorites()->count(),
            'notification' => $notification,
            'form' => $form->createView(),
            'userCanRate' => !$notification->getSenderIsRated(),
//            'voted' => $this->getDoctrine()->getRepository(UserToUserRate::class)->getUserVotedValue($notification->getSender()),
            'rate' => $this->getDoctrine()->getRepository(UserToUserRate::class)->getUserRateValue($notification->getSender()),
        ]);
    }

    /**
     * @Route("/profile/market/notification/offer/{id}", name="notification_offer_detail")
     * @throws NotFoundException
     */
    public function detailOfferNotification(int $id): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();
        $notification = $this->getDoctrine()->getRepository(KitAgreementNotification::class)->find($id);
        $this->checkNotificationExistsAndBelongsCurrentUser($notification);
        $this->setIsReadIfUnreadedNotification($notification);
        $daysLeftForApprove = new DateTime($notification->getCreatedAt()->format('Y-m-d H:i:s'));
        $daysLeftForApprove->modify('+3 days');
        $allCount = $this->notificationDataService->getUserNotificationCounters($user);

        return $this->render('profile_marketplace/detail-offer-review-notification.html.twig', [
            'activeUserNotificationCount' => $allCount['activeUserNotificationCount'],
            'trashedUserNotificationCount' => $allCount['trashedUserNotificationCount'],
            'userFavoritesCount' => $user->getFavorites()->count(),
            'notification' => $notification,
            'daysLeftForApprove' => $daysLeftForApprove
        ]);
    }

    /**
     * @Route("/profile/market/notification/approve/offer/{id}", name="notification_offer_approve")
     * @throws NotFoundException
     */
    public function approveKitAgreementNotification(int $id): Response
    {
        //todo make event from here
        /** @var User|null $user */
        $notification = $this->getDoctrine()->getRepository(KitAgreementNotification::class)->find($id);
        $this->checkNotificationExistsAndBelongsCurrentUser($notification);
        $kitAgreementNotificationEvent = new CommodityKitApprovingEvent();
        $kitAgreementNotificationEvent->setNotification($notification);
        $this->eventDispatcher->dispatch($kitAgreementNotificationEvent);

        $this->addFlash('html.success', 'Ви підтвердили згоду на створення спільної пропозиції.' .
            ' Якщо всі участники спільної пропозиції також підтвердять свою участь у ній, то ' .
            'спільна пропозиція <q><b>' . $notification->getCommodity()->getTitle() .
            '</b></q> стане активною та всі учасники будуть сповіщенні');

        return $this->redirectToRoute('my_notifications');
    }



    /**
     * @Route("/profile/market/notification-delete/{id}", name="notification_delete")
     * @throws NotFoundException
     */
    public function deleteUserNotification(int $id): Response
    {
        /** @var User|null $user */
        $notification = $this->getDoctrine()->getRepository(Notification::class)->find($id);
        $this->checkNotificationExistsAndBelongsCurrentUser($notification);
        if ($notification->getIsActive() === false || $notification->getIsActive() === null) {
            throw new NotFoundException();
        } else {
            $notification->setIsActive(false);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Повідомлення успішно переміщено до кошика');
            return $this->redirectToRoute('my_notifications');
        }

    }

    /**
     * @Route("/profile-marketplace/notifications/batch/delete", name="notification_batch_delete")
     */
    public function batchDeleteNotifications(Request $request): ?Response
    {
        $this->validateXmlHttpRequest($request);

        if ($request->isMethod('GET')) {
            return new JsonResponse($this->getSwalPopupMessageData(true), 200);
        } else {
            /** @var User|null $user */
            $user = $this->getUser();
            if (!$user) {
                return $this->redirectToRoute('login');
            }
            $this->toggleIsActiveFieldUserNotification($request, $user, false);

            return new JsonResponse([], 200);
        }
    }

    /**
     * @Route("/profile-marketplace/notifications/batch/restore", name="notification_batch_restore")
     */
    public function batchRestoreNotifications(Request $request): ?Response
    {
        $this->validateXmlHttpRequest($request);

        if ($request->isMethod('GET')) {
            return new JsonResponse($this->getSwalPopupMessageData(false), 200);
        } else {
            /** @var User|null $user */
            $user = $this->getUser();
            if (!$user) {
                return $this->redirectToRoute('login');
            }
            $this->toggleIsActiveFieldUserNotification($request, $user, true);

            return new JsonResponse([], 200);
        }
    }

    /**
     * @Route("/profile-marketplace/notifications/trash", name="my_notifications_trash")
     */
    public function listNotificationsTrash(Request $request, PaginatorInterface $paginator): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();
        $allCount = $this->notificationDataService->getUserNotificationCounters($user);
        $all = $request->query->all();
        $items = $this->notificationDataService->getFilteredNotifications($request, $user, $paginator, false);
        $pageValueIncome = (int)($requestData['page'] ?? 0);
        $page = $pageValueIncome > 0 ? $pageValueIncome : 1;
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'content' => $this->render('notification/notification-block.html.twig',
                    [
                        'items' => $items,
                        'currentPage' => $page,
                        'paginationName' => 'page',
                    ])->getContent(),
            ], Response::HTTP_OK);
        } else {
            return $this->render('profile_marketplace/my-notifications-trash.html.twig', [
                'appliedQueryParams' => [
                    self::QUERY_PARAMETER_SEARCH => $this->notificationDataService->getSearch($all),
                    self::QUERY_PARAMETER_FILTER_TYPE => $this->notificationDataService->getTypeFilter($all),
                    self::QUERY_PARAMETER_PAGE => $this->notificationDataService->getPage($all),
                ],
                'filter' => [
                    self::QUERY_PARAMETER_SEARCH => $this->notificationDataService->getSearch($all),
                    'typeValues' => self::QUERY_PARAMETER_FILTER_TYPE_VALUES,
                ],
                'items' => $items,
                'currentPage' => $page,
                'paginationName' => 'page',
                'activeUserNotificationCount' => $allCount['activeUserNotificationCount'],
                'trashedUserNotificationCount' => $allCount['trashedUserNotificationCount'],
                'userFavoritesCount' => $user->getFavorites()->count(),
                'trashFlag' => true,
                'listAjaxUrl' => $this->generateUrl('my_notifications_trash',
                    [
                        self::QUERY_PARAMETER_SEARCH => 'SEARCH_VALUE',
                        self::QUERY_PARAMETER_FILTER_TYPE => 'TYPE_VALUES',
                        self::QUERY_PARAMETER_PAGE => 'PAGE_VALUE',
                    ]),
            ]);
        }
    }

    /**
     * @Route("/profile/market/clear-trash-bucket/", name="notification_bucket_clear")
     */
    public function softDeleteUserNotification(Request $request)
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['message' => 'Bad Request'], 400);
        }
        $this->addFlash('success', 'Кошик успішно очищенно');
        $this->getDoctrine()->getRepository(Notification::class)->softDeleteUserNotification($user);
        return $this->redirectToRoute('my_notifications');
    }

    private function validateXmlHttpRequest(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse('Bad Request', 400);
        }
    }

    private function getSwalPopupMessageData(bool $isDeleteConfirmation): array
    {
        $isDeleteConfirmation === true
            ? $data = [
            'title' => 'Ви впевнені, що бажаєте видалити обрані повідомлення?',//todo may be changed to data From admin panel
            'confirmButtonText' => 'Так, видалити повідомлення',
            'cancelButtonText' => 'Скасувати'
        ]
            : $data = [
            'title' => 'Ви впевнені, що бажаєте відновити обрані повідомлення?',
            'confirmButtonText' => 'Так, відновити повідомлення',
            'cancelButtonText' => 'Скасувати'
        ];
        return $data;
    }

    private function toggleIsActiveFieldUserNotification(Request $request, User $user, bool $setIsActiveTo): void
    {
        $notificationIds = $request->request->get('notificationsIds');

        if (!empty($notificationIds)) {
            if ($setIsActiveTo === true) {
                $this->getDoctrine()->getRepository(Notification::class)
                    ->batchRestoreUserNotification($user, (array)$notificationIds);
                $this->addFlash('success', 'Повідомлення успішно відновленні, ви можете їх переглянути у вхідних.');
            } else {
                $this->getDoctrine()->getRepository(Notification::class)
                    ->batchDeleteUserNotification($user, (array)$notificationIds);
                $this->addFlash('success', 'Повідомлення успішно видаленні, ви можете їх переглянути у кошику.');
            }
        } else {
            $this->addFlash('error', 'Щось пішло не так при опрацюванні ваших повідомлень');
        }
    }

    /**
     * @throws NotFoundException
     */
    private function checkNotificationExistsAndBelongsCurrentUser(?Notification $notification)
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$notification || $notification->getReceiver() !== $user) {
            throw new NotFoundException();
        }
    }

    private function setIsReadIfUnreadedNotification(Notification $notification): void
    {
        if ($notification->getIsRead() === false || $notification->getIsRead() === null) {
            $notification->setIsRead(true);
            $this->getDoctrine()->getManager()->flush();
        }
    }
}
