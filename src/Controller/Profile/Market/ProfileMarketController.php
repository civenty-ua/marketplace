<?php
declare(strict_types=1);

namespace App\Controller\Profile\Market;

use Doctrine\ORM\QueryBuilder;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Knp\Component\Pager\{
    PaginatorInterface,
    Pagination\PaginationInterface,
};
use App\Service\{
    Notification\NotificationDataService,
    Notification\SystemNotificationSender,
    Market\UserPublicationsCountProvider,
};
use App\Service\Market\CommodityActivity\CommodityActivityManager;
use App\Controller\AuthRequiredControllerInterface;
use App\Entity\User;
/**
 * Profile market abstract controller.
 *
 * @package App\Controller\Profile
 */
abstract class ProfileMarketController extends AbstractController implements AuthRequiredControllerInterface
{
    protected TranslatorInterface           $translator;
    protected PaginatorInterface            $paginator;
    protected EventDispatcherInterface      $eventDispatcher;
    protected NotificationDataService       $notificationDataService;
    protected SystemNotificationSender      $systemNotificationSender;
    protected UserPublicationsCountProvider $userPublicationsManager;
    protected CommodityActivityManager      $commodityActivityManager;

    public function __construct(
        TranslatorInterface             $translator,
        PaginatorInterface              $paginator,
        EventDispatcherInterface        $eventDispatcher,
        NotificationDataService         $notificationDataService,
        SystemNotificationSender        $systemNotificationSender,
        UserPublicationsCountProvider   $userPublicationsManager,
        CommodityActivityManager        $commodityActivityManager
    ) {
        $this->translator               = $translator;
        $this->paginator                = $paginator;
        $this->eventDispatcher          = $eventDispatcher;
        $this->notificationDataService  = $notificationDataService;
        $this->systemNotificationSender = $systemNotificationSender;
        $this->userPublicationsManager  = $userPublicationsManager;
        $this->commodityActivityManager = $commodityActivityManager;
    }
    /**
     * @inheritdoc
     */
    protected function render(string $view, array $parameters = [], Response $response = null): Response
    {
        /** @var User|null $currentUser */
        $currentUser            = $this->getUser();
        $notificationCount      = $this->notificationDataService->getUserNotificationCounters($currentUser);

        return parent::render(
            $view,
            array_merge($parameters, [
                'allUserNotificationCount'      => $notificationCount['allUserNotificationCount'],
                'unreadUserNotificationCount'   => $notificationCount['unreadUserNotificationCount'],
            ]),
            $response
        );
    }
    /**
     * Run pagination.
     *
     * @param   QueryBuilder    $queryBuilder   Query builder.
     * @param   int             $page           Current page.
     * @param   int             $pageSize       Page size.
     *
     * @return  PaginationInterface             Paginated items.
     */
    protected function paginate(QueryBuilder $queryBuilder, int $page, int $pageSize): PaginationInterface
    {
        return $this->paginator->paginate(
            $queryBuilder,
            $page,
            $pageSize,
            [
                PaginatorInterface::PAGE_OUT_OF_RANGE => PaginatorInterface::PAGE_OUT_OF_RANGE_FIX
            ]
        );
    }
}
