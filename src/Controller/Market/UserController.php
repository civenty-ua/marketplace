<?php
declare(strict_types=1);

namespace App\Controller\Market;

use DateTime;
use InvalidArgumentException;
use Doctrine\{
    ORM\QueryBuilder,
    Persistence\ObjectRepository,
};
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    JsonResponse,
};
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Knp\Component\Pager\{
    PaginatorInterface,
    Pagination\PaginationInterface
};
use App\Repository\Market\{
    CommodityProductRepository,
    CommodityServiceRepository,
    CommodityKitRepository,
};
use App\Service\Notification\SystemNotificationSender;
use App\Form\Market\ProfileUserToUserReviewFormType;
use App\Entity\{
    User,
    UserToUserRate,
    UserToUserReview,
    Market\Commodity,
    Market\CommodityProduct,
    Market\CommodityService,
    Market\CommodityKit,
};
use App\Entity\Market\Notification\{
    BidOffer,
    OfferReview,
};
/**
 * market user controller.
 *
 * @package App\Controller
 */
class UserController extends AbstractController
{
    private const COMMODITIES_PAGE_SIZE     = 6;
    private const COMMENTS_PAGE_SIZE        = 12;
    private const SAME_USERS_LIMIT          = 9;
    private const COMMODITIES_ACTION_MAIN   = 'buy';
    private const COMMODITIES_ACTIONS       = [
        'view',
        'offerPrice',
        'toFavoriteToggle',
        'edit',
    ];
    private const USER_ACTION_MAIN          = 'view';
    private const USER_ACTIONS              = [
        'view',
        'toFavoriteToggle',
    ];

    private PaginatorInterface          $paginator;
    private SystemNotificationSender    $systemNotificationSender;

    public function __construct(
        PaginatorInterface              $paginator,
        SystemNotificationSender        $systemNotificationSender
    ) {
        $this->paginator                    = $paginator;
        $this->systemNotificationSender     = $systemNotificationSender;
    }

    /**
     * @Route("/marketplace/user/{id}", name="market_user_detail")
     */
    public function detail(int $id, Request $request): Response
    {
        /** @var User|null $currentUser */
        $currentUser            = $this->getUser();
        $user                   = $this->getItemDetail($id);
        $appliedFilter          = $this->parsePageFilter($request);
        $commoditiesTotalCount  = [];

        if (!$user) {
            throw new NotFoundHttpException();
        }

        foreach (Commodity::getAvailableTypes() as $type) {
            $commoditiesTotalCount[$type] = $this
                ->getCommodityRepository($type)
                ->getTotalCount(
                    $currentUser,
                    [
                        'user' => $user->getId(),
                    ]
                );
        }

        if ($commoditiesTotalCount[$appliedFilter['commodityType']] === 0) {
            foreach ($commoditiesTotalCount as $type => $value) {
                if ($value> 0) {
                    $appliedFilter['commodityType'] = $type;
                    break;
                }
            }
        }

        $bidOfferExist =  $this
                ->getDoctrine()
                ->getRepository(BidOffer::class)
                ->findOneBy([
                    'sender' => $currentUser,
                    'receiver' => $user
                ]) != null;
        $userAlreadyRated = $this
                ->getDoctrine()
                ->getRepository(UserToUserRate::class)
                ->findOneBy([
                    'user' => $currentUser,
                    'targetUser' => $user
                ]) != null;

        return $this->render('market/user/itemDetail.html.twig', [
            'user' => $user,
            'commodities' => [
                'itemsTotalCount' => $commoditiesTotalCount,
                'filter' => $appliedFilter,
                'items' => $this->getUserCommodities(
                    $user,
                    $appliedFilter['commodityType'],
                    $appliedFilter
                ),
                'availableSortValues' => $this->getCommoditySortAvailableValues($appliedFilter['commodityType']),
                'actions' => self::COMMODITIES_ACTIONS,
                'mainAction' => self::COMMODITIES_ACTION_MAIN,
            ],
            'sameUsers' => [
                'items' => $this->getUserSameSellers($user),
                'actions' => self::USER_ACTIONS,
                'mainAction' => self::USER_ACTION_MAIN,
            ],
            'userReviews' => [
                'items' => $this->getPaginatedReviews($user, $appliedFilter),
                'form' => $currentUser ? $this->createForm(ProfileUserToUserReviewFormType::class, new UserToUserReview(), [
                    'action' => $this->generateUrl('market_user_to_user_form_handle', ['id' => $user->getId()])
                ])->createView() : null,
                'page' => $appliedFilter['reviewPage'] ?? 1,
                'rebuildUrl' => $this->generateUrl('market_user_review_list_rebuild', [
                    'id' => $user->getId(),
                ])
            ],
            'userCanRate' => $bidOfferExist && !$userAlreadyRated,
            'rate' => $this
                ->getDoctrine()
                ->getRepository(UserToUserRate::class)
                ->getUserRateValue($user)
        ]);
    }

    /**
     * @Route("/marketplace/user-review/{id}", name="market_user_to_user_form_handle")
     */
    public function handleUserToUserFormRequest(Request $request, int $id): ?Response
    {
        $user = $this->getItemDetail($id);

        if ($this->getUser() && $user) {

            $userToUserReview = new UserToUserReview();
            $form = $this->createForm(ProfileUserToUserReviewFormType::class, $userToUserReview);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->getErrors()->count() === 0) {
                $entityManager = $this->getDoctrine()->getManager();
                $userToUserReview->setUser($this->getUser());
                $userToUserReview->setTargetUser($user);
                $userToUserReview->setCreatedAt(new DateTime('now'));
                $userToUserReview->setUpdatedAt(new DateTime('now'));
                $entityManager->persist($userToUserReview);

                $this->systemNotificationSender->sendSingleNotification([
                    'receiver' => $user,
                    'message' => "Шановний $user, користувач {$this->getUser()} залишив про вас відгук."
                ]);
                $entityManager->flush();

                return $this->redirectToRoute('market_user_detail', ['id' => $user->getId()]);
            } else {
                return new RedirectResponse($request->headers->get('referer'));
            }
        }

        return null;
    }
    /**
     * @Route(
     *     "/marketplace/user/{id}/commodity-tab-rebuild",
     *     name     = "market_user_commodity_tab_rebuild",
     *     methods  = "POST"
     * )
     */
    public function commodityTabAjaxRebuild(int $id, Request $request): Response
    {
        $user = $this->getItemDetail($id);
        $appliedFilter = array_merge($this->parsePageFilter(new Request()), [
            'commodityType' => $request->request->get('commodityType'),
        ]);

        if (!$user) {
            throw new NotFoundHttpException();
        }

        return new JsonResponse([
            'url' => $this->generateUrl('market_user_detail', [
                'id' => $id,
                'commodityType' => $appliedFilter['commodityType'],
            ]),
            'itemsList' => $this
                ->render('market/user/commoditiesList.html.twig', [
                    'user' => $user,
                    'commodityType' => $appliedFilter['commodityType'],
                    'items' => $this->getUserCommodities(
                        $user,
                        $appliedFilter['commodityType'],
                        $appliedFilter
                    ),
                    'search' => null,
                    'availableSortValues' => $this->getCommoditySortAvailableValues($appliedFilter['commodityType']),
                    'sort' => $appliedFilter['sortField'],
                    'page' => $appliedFilter['page'],
                    'actions' => self::COMMODITIES_ACTIONS,
                    'mainAction' => self::COMMODITIES_ACTION_MAIN,
                ])
                ->getContent(),
        ]);
    }

    /**
     * @Route(
     *     "/marketplace/user/{id}/user-review-list-rebuild",
     *     name     = "market_user_review_list_rebuild",
     *     methods  = "POST"
     * )
     */
    public function userReviewListAjaxRebuild(int $id, Request $request): Response
    {
        $user = $this->getItemDetail($id);
        $appliedFilter = $this->parsePageFilter($request);

        if (!$user) {
            throw new NotFoundHttpException();
        }

        $reviews = $this->getPaginatedReviews($user, $appliedFilter);

        return new JsonResponse([
            'url' => $this->generateUrl('market_user_detail', array_merge($appliedFilter, [
                'id' => $id,
            ])),
            'itemsList' => $this
                ->render('market/user-review-item-block.html.twig', [
                    'items' => $reviews,
                    'user' => $user,
                    'currentPage' => $appliedFilter['reviewPage'],
                    'reviewRebuildUrl' => $this->generateUrl(
                        'market_user_review_list_rebuild', ['id' => $user->getId()]),
                ])
                ->getContent(),
        ]);
    }

    /**
     * @Route(
     *     "/marketplace/user/{id}/commodities-list-rebuild",
     *     name     = "market_user_commodities_list_rebuild",
     *     methods  = "POST"
     * )
     */
    public function commoditiesListAjaxRebuild(int $id, Request $request): Response
    {
        $user           = $this->getItemDetail($id);
        $appliedFilter  = $this->parsePageFilter($request);

        if (!$user) {
            throw new NotFoundHttpException();
        }

        return new JsonResponse([
            'url'       => $this->generateUrl('market_user_detail', array_merge($appliedFilter, [
                'id' => $id,
            ])),
            'itemsList' => $this
                ->render('market/commodity/items.html.twig', [
                    'items'             => $this->getUserCommodities(
                        $user,
                        $appliedFilter['commodityType'],
                        $appliedFilter
                    ),
                    'currentPage'       => $appliedFilter['page'],
                    'paginationName'    => 'page',
                    'actions'           => self::COMMODITIES_ACTIONS,
                    'mainAction'        => self::COMMODITIES_ACTION_MAIN,
                ])
                ->getContent(),
        ]);
    }

    /**
     * @Route(
     *     "/marketplace/user/estimate/{id}",
     *     name     = "user_estimate",
     *     methods  = "POST"
     * )
     */
    public function estimateUser(Request $request, int $id): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['message' => 'Bad Request'], 400);
        }

        if (!$this->getUser()) {
            return new JsonResponse([
                'title' => 'Щоб оцінити користувача потрібно бути авторизованим',
                'login' => $this->generateUrl('login')
            ], 401);
        }

        $targetUser = $this->getDoctrine()->getRepository(User::class)->find($id);

        if (!$targetUser) {
            return new JsonResponse(['message' => 'User not found'], 404);
        }

        $score = json_decode($request->getContent());
        $uriParts = explode('/', $request->headers->get('referer'));
        $this->createUserToUserRate($targetUser, $score);

        if (str_contains($request->headers->get('referer'), 'review')) {
            $reviewNotificationId = (int)array_pop($uriParts);
            $reviewNotification = $this->getDoctrine()->getRepository(OfferReview::class)->find($reviewNotificationId);
            if ($reviewNotification) {
                $reviewNotification->setSenderIsRated(true);
            }
        }

        $this->getDoctrine()->getManager()->flush();
        return new JsonResponse([
            'title' => "Ви поставили  оцінку $score, користувачеві {$targetUser->getName()}",
            'message' => 'success',
        ], 200);
    }

    /**
     * Parse page filter from request.
     *
     * @param Request $request Request.
     *
     * @return  array                       Parsed filter.
     */
    private function parsePageFilter(Request $request): array
    {
        $requestData = $request->getMethod() === 'POST'
            ? $request->request->all()
            : $request->query->all();

        $commodityTypeValue = $requestData['commodityType'] ?? null;
        $typesAvailableValues = Commodity::getAvailableTypes();
        $commodityType = in_array($commodityTypeValue, $typesAvailableValues)
            ? $commodityTypeValue
            : array_values($typesAvailableValues)[0];

        $searchValue = (string)($requestData['search'] ?? '');
        $search = strlen($searchValue) > 0 ? $searchValue : null;

        $sortValue = $requestData['sortField'] ?? null;
        $sortAvailableValues = $this->getCommoditySortAvailableValues($commodityType);
        $sort = in_array($sortValue, $sortAvailableValues)
            ? $sortValue
            : $sortAvailableValues[0];

        $pageValue = (int)($requestData['page'] ?? 0);
        $page = $pageValue > 0 ? $pageValue : 1;
        $reviewPageValue = (int)($requestData['reviewPage'] ?? 0);
        $reviewPage = $reviewPageValue > 0 ? $reviewPageValue : 1;

        return [
            'commodityType' => $commodityType,
            'search' => $search,
            'sortField' => $sort,
            'page' => $page,
            'reviewPage' => $reviewPage
        ];
    }

    /**
     * Get user for detail page.
     *
     * @return User|null                    User, ia any
     */
    private function getItemDetail(int $id): ?User
    {
        /**
         * @var User|null $currentUser
         * @var QueryBuilder $itemsQuery
         */
        $currentUser = $this->getUser();
        $itemsQuery = $this
            ->getDoctrine()
            ->getRepository(User::class)
            ->marketListFilter(
                $currentUser,
                null,
                ['id' => $id],
            );

        return $itemsQuery->getQuery()->getResult()[0] ?? null;
    }

    /**
     * Get repository for given commodity type.
     *
     * @param string $commodityType Commodity type.
     *
     * @return  ObjectRepository            Repository.
     */
    private function getCommodityRepository(string $commodityType): ObjectRepository
    {
        $entityManager = $this->getDoctrine();

        switch ($commodityType) {
            case Commodity::TYPE_PRODUCT:
                return $entityManager->getRepository(CommodityProduct::class);
            case Commodity::TYPE_SERVICE:
                return $entityManager->getRepository(CommodityService::class);
            case Commodity::TYPE_KIT:
                return $entityManager->getRepository(CommodityKit::class);
            default:
                throw new InvalidArgumentException();
        }
    }

    /**
     * Get commodities total count for given user.
     *
     * @param string $commodityType Commodity type.
     *
     * @return  string[]                    Sort available values.
     */
    private function getCommoditySortAvailableValues(string $commodityType): array
    {
        switch ($commodityType) {
            case Commodity::TYPE_PRODUCT:
                return CommodityProductRepository::getListFilterAvailableSortValues();
            case Commodity::TYPE_SERVICE:
                return CommodityServiceRepository::getListFilterAvailableSortValues();
            case Commodity::TYPE_KIT:
                return CommodityKitRepository::getListFilterAvailableSortValues();
            default:
                throw new InvalidArgumentException();
        }
    }

    /**
     * Get commodities for given user and commodity type.
     *
     * @param   User    $user               User.
     * @param   string  $commodityType      Commodity type.
     * @param   array   $appliedFilter      Filter.
     *
     * @return  PaginationInterface         Items (pagination processed).
     */
    private function getUserCommodities(
        User   $user,
        string $commodityType,
        array  $appliedFilter = []
    ): PaginationInterface {
        /** @var User|null $currentUser */
        $currentUser    = $this->getUser();
        $queryBuilder   = $this
            ->getCommodityRepository($commodityType)
            ->listFilter(
                $currentUser,
                $appliedFilter['sortField'] ?? null,
                [
                    'search'    => $appliedFilter['search'] ?? null,
                    'user'      => $user->getId(),
                ]
            );

        return $this->paginator->paginate(
            $queryBuilder,
            $appliedFilter['page'],
            self::COMMODITIES_PAGE_SIZE,
            [
                PaginatorInterface::PAGE_OUT_OF_RANGE => PaginatorInterface::PAGE_OUT_OF_RANGE_FIX
            ]
        );
    }
    /**
     * Get same sellers for given user.
     *
     * @param   User $user                  User.
     *
     * @return  User[]                      Same sellers.
     */
    private function getUserSameSellers(User $user): iterable
    {
        /** @var User|null $currentUser */
        $currentUser                = $this->getUser();
        $sameCommoditiesId          = $this->getUserSameCommoditiesId($user);
        $sameSellersQueryBuilder    = $this
            ->getDoctrine()
            ->getRepository(User::class)
            ->marketListFilter(
                $currentUser,
                null,
                [
                    'commodities' => $sameCommoditiesId,
                ]
            );

        return $this->paginator->paginate(
            $sameSellersQueryBuilder,
            1,
            self::SAME_USERS_LIMIT,
            [
                PaginatorInterface::PAGE_OUT_OF_RANGE => PaginatorInterface::PAGE_OUT_OF_RANGE_FIX
            ]
        )->getItems();
    }
    /**
     * Get user same commodities ID set.
     *
     * @param   User $user                  User.
     *
     * @return  int[]
     */
    private function getUserSameCommoditiesId(User $user): iterable
    {
        /** @var QueryBuilder $queryBuilder */
        $commodityTypes     = [
            Commodity::TYPE_PRODUCT,
            Commodity::TYPE_SERVICE,
        ];
        $queryBuilder       = $this
            ->getDoctrine()
            ->getRepository(Commodity::class)
            ->listFilter(
                null,
                null,
                [
                    'user'          => $user->getId(),
                    'commodityType' => $commodityTypes,
                ]
            );
        $categories         = array_map(function(Commodity $commodity): int {
            return $commodity->getCategory()->getId();
        }, $queryBuilder->getQuery()->getResult());

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder       = $this
            ->getDoctrine()
            ->getRepository(Commodity::class)
            ->listFilter(
                null,
                null,
                [
                    '!user'         => $user->getId(),
                    'commodityType' => $commodityTypes,
                    'category'      => count($categories) > 0 ? array_unique($categories) : ['none'],
                ]
            );
        $alias              = $queryBuilder->getRootAliases()[0];

        $queryBuilder->select("$alias.id");
        $sameCommodities    = array_map(function(array $data): int {
            return $data['id'];
        }, $queryBuilder->getQuery()->getResult());

        return array_unique($sameCommodities);
    }

    private function createUserToUserRate(UserInterface $targetUser, float $score)
    {
        $userToUserRate = new UserToUserRate(); //todo made field for one estimation per offerReviewNotification
        $userToUserRate->setUser($this->getUser());
        $userToUserRate->setTargetUser($targetUser);
        $userToUserRate->setRate($score);
        $userToUserRate->setCreatedAt(new \DateTime());
        $userToUserRate->setUpdatedAt(new \DateTime());

        $this->getDoctrine()->getManager()->persist($userToUserRate);
        $this->getDoctrine()->getManager()->flush();
        $this->systemNotificationSender->sendSingleNotification([
            'receiver' => $targetUser,
            'title' => "Оцінка від {$this->getUser()->getName()}",
            'message' => "Шановний {$targetUser->getName()}, користувач {$this->getUser()->getName()} поставив вам оцінку {$score}"
        ]);
    }

    private function getPaginatedReviews(User $targetUser, array $appliedFilter): PaginationInterface
    {
        $reviewsQuery = $this->getDoctrine()->getRepository(UserToUserReview::class)
            ->createQueryBuilder('utur')
            ->where('utur.targetUser = :targetUser')
            ->andWhere('utur.reviewText is not null')
            ->setParameter('targetUser', $targetUser)
            ->orderBy('utur.createdAt', 'DESC')
            ->getQuery();

        return $this->paginator->paginate(
            $reviewsQuery,
            $appliedFilter['reviewPage'] ?? 1,
            self::COMMENTS_PAGE_SIZE,
            [
                PaginatorInterface::PAGE_OUT_OF_RANGE => PaginatorInterface::PAGE_OUT_OF_RANGE_FIX
            ]
        );
    }
}
