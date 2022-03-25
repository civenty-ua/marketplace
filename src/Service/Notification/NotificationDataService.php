<?php

namespace App\Service\Notification;

use App\Entity\Market\Notification\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class NotificationDataService
{
    private const QUERY_PARAMETER_SEARCH = 'search';
    private const QUERY_PARAMETER_FILTER_TYPE = 'type';
    private const QUERY_PARAMETER_PAGE = 'page';
    private const LIST_PAGE_SIZE = 30;

    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getUserNotificationCounters(UserInterface $user): array
    {
        $allCount['allUserNotificationCount'] = $this->entityManager->getRepository(Notification::class)
            ->countAllUserNotifications($user);
        $allCount['activeUserNotificationCount'] = $this->entityManager->getRepository(Notification::class)
            ->countAllActiveUserNotification($user);
        $allCount['trashedUserNotificationCount'] = $this->entityManager->getRepository(Notification::class)
            ->countAllTrashedUserNotification($user);
        $allCount['unreadUserNotificationCount'] = $this->entityManager->getRepository(Notification::class)
            ->countAllUnreadUserNotification($user);

        return $allCount;
    }

    public function getFilteredNotifications(
        Request            $request,
        UserInterface      $user,
        PaginatorInterface $paginator,
        bool               $isActive
    ):\Knp\Component\Pager\Pagination\PaginationInterface
    {
        $all = $request->query->all();
        $search = $this->getSearch($all);
        $typeFilter = $this->getTypeFilter($all);
        $page = $this->getPage($all);

        $items = $this->entityManager->getRepository(Notification::class)
            ->getFilteredNotifications($search, $typeFilter, $user, $isActive);

        return $paginator->paginate(
            $items,
            $request->query->getInt(self::QUERY_PARAMETER_PAGE, 1),
            self::LIST_PAGE_SIZE
        );
    }

    public function getTypeFilter(array $all): ?int
    {
        !empty($all[self::QUERY_PARAMETER_FILTER_TYPE])
            ? $typeFilter = (int)($all[self::QUERY_PARAMETER_FILTER_TYPE])
            : $typeFilter = null;
        return $typeFilter;
    }

    public function getSearch(array $all): ?string
    {
        !empty($all[self::QUERY_PARAMETER_SEARCH])
            ? $search = htmlspecialchars($all[self::QUERY_PARAMETER_SEARCH])
            : $search = null;
        return $search;
    }

    public function getPage(array $all): ?int
    {
        !empty($all[self::QUERY_PARAMETER_PAGE])
            ? $page = (int)$all[self::QUERY_PARAMETER_PAGE]
            : $page = null;
        return $page;
    }
}