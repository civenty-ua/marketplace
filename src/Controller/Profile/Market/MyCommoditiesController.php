<?php
declare(strict_types = 1);

namespace App\Controller\Profile\Market;

use Throwable;
use DateTime;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\{
    AccessDeniedHttpException,
    BadRequestHttpException,
    NotFoundHttpException,
};
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    JsonResponse,
};
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\Pagination\PaginationInterface;
use App\Service\Market\CommodityActivity\PublicationsCountControlException;
use App\Event\Commodity\{
    CommodityActivationEvent,
    CommodityDeactivationEvent,
    CommodityCreateEvent,
    CommodityUpdateEvent,
    CommodityKitDeactivationByCoAuthorEvent,
};
use App\Form\Profile\Market\{
    ProductFormType,
    ServiceFormType,
    KitFormType,
};
use App\Repository\Market\{
    CommodityProductRepository,
    CommodityServiceRepository,
    CommodityKitRepository,
};
use App\Entity\{
    District,
    Locality,
    Region,
    User,
    Market\Category,
    Market\Commodity,
    Market\CommodityProduct,
    Market\CommodityService,
    Market\CommodityKit,
};
/**
 * @package App\Controller\Profile
 */
class MyCommoditiesController extends ProfileMarketController
{
    private const MY_COMMODITIES_PAGE_SIZE      = 24;
    private const MY_COMMODITIES_ACTIONS        = [
        'edit',
        'activation',
        'kitLeaving',
    ];
    private const MY_COMMODITIES_ACTION_MAIN    = 'edit';
    private const KIT_COMMODITIES_PAGE_SIZE     = 6;
    /**
     * @Route("/profile/market/my-goods", name="market_profile_my_commodities_product")
     */
    public function myProducts(Request $request): Response
    {
        return $this->renderMyCommodities($request, Commodity::TYPE_PRODUCT);
    }
    /**
     * @Route(
     *     "/profile/market/my-goods/list-rebuild",
     *     name     = "market_profile_my_commodities_product_list_rebuild",
     *     methods  = "POST"
     * )
     */
    public function myProductsAjaxRebuild(Request $request): Response
    {
        return $this->renderMyCommoditiesAjaxRebuild($request, Commodity::TYPE_PRODUCT);
    }
    /**
     * @Route("/profile/market/my-goods/create", name="market_profile_my_commodities_product_create")
     */
    public function myProductsCreate(Request $request): Response
    {
        return $this->renderCommodityForm($request, Commodity::TYPE_PRODUCT);
    }
    /**
     * @Route("/profile/market/my-goods/edit/{id}", name="market_profile_my_commodities_product_edit")
     */
    public function myProductsEdit(int $id, Request $request): Response
    {
        return $this->renderCommodityForm($request, Commodity::TYPE_PRODUCT, $id);
    }
    /**
     * @Route("/profile/market/my-services", name="market_profile_my_commodities_service")
     */
    public function myServices(Request $request): Response
    {
        return $this->renderMyCommodities($request, Commodity::TYPE_SERVICE);
    }
    /**
     * @Route(
     *     "/profile/market/my-services/list-rebuild",
     *     name     = "market_profile_my_commodities_service_list_rebuild",
     *     methods  = "POST"
     * )
     */
    public function myServicesAjaxRebuild(Request $request): Response
    {
        return $this->renderMyCommoditiesAjaxRebuild($request, Commodity::TYPE_SERVICE);
    }
    /**
     * @Route("/profile/market/my-services/create", name="market_profile_my_commodities_service_create")
     */
    public function myServicesCreate(Request $request): Response
    {
        return $this->renderCommodityForm($request, Commodity::TYPE_SERVICE);
    }
    /**
     * @Route("/profile/market/my-services/edit/{id}", name="market_profile_my_commodities_service_edit")
     */
    public function myServicesEdit(int $id, Request $request): Response
    {
        return $this->renderCommodityForm($request, Commodity::TYPE_SERVICE, $id);
    }
    /**
     * @Route("/profile/market/my-proposals", name="market_profile_my_commodities_kit")
     */
    public function myKits(Request $request): Response
    {
        return $this->renderMyCommodities($request, Commodity::TYPE_KIT);
    }
    /**
     * @Route(
     *     "/profile/market/my-proposals/list-rebuild",
     *     name     = "market_profile_my_commodities_kit_list_rebuild",
     *     methods  = "POST"
     * )
     */
    public function myKitsAjaxRebuild(Request $request): Response
    {
        return $this->renderMyCommoditiesAjaxRebuild($request, Commodity::TYPE_KIT);
    }
    /**
     * @Route("/profile/market/my-proposals/create", name="market_profile_my_commodities_kit_create")
     */
    public function myKitsCreate(Request $request): Response
    {
        return $this->renderCommodityForm($request, Commodity::TYPE_KIT);
    }
    /**
     * @Route("/profile/market/my-proposals/edit/{id}", name="market_profile_my_commodities_kit_edit")
     */
    public function myKitsEdit(int $id, Request $request): Response
    {
        return $this->renderCommodityForm($request, Commodity::TYPE_KIT, $id);
    }
    /**
     * @Route(
     *     "/profile/market/commodity/{commodityType}/{id}/attributes-rebuild",
     *     name     = "market_profile_commodity_attributes_rebuild",
     *     methods  = "POST"
     * )
     */
    public function commodityAttributesAjaxRebuild(
        string  $commodityType,
        int     $id,
        Request $request
    ): Response {
        /** @var CommodityProduct|CommodityService $commodity */
        $commodity = $id > 0
            ? $this->getCommodityById($commodityType, $id)
            : $this->getNewCommodity($commodityType);
        $categoryId = (int) $request->get('category');
        $category   = $categoryId > 0
            ? $this->getDoctrine()->getRepository(Category::class)->find($categoryId)
            : null;

        if ($id > 0 && !$commodity) {
            throw new NotFoundHttpException();
        }

        $commodity->setCategory($category);
        $formView = $this->createCommodityForm($commodity)->createView();

        return new JsonResponse([
            'category'      => $this
                ->render('profile/market/myCommodities/category.html.twig', [
                    'form'      => $formView,
                    'editable'  => $this->getCommodityFullyEditableState($commodity),
                ])
                ->getContent(),
            'attributes'    => $this
                ->render('profile/market/myCommodities/attributesForm.html.twig', [
                    'form'          => $formView,
                    'commodityType' => $commodityType,
                ])
                ->getContent(),
        ]);
    }
    /**
     * @Route(
     *     "/profile/market/product/localities-fields-rebuild",
     *     name     = "market_profile_commodity_localities_fields_rebuild",
     *     methods  = "POST"
     * )
     */
    public function productLocalitiesFieldsAjaxRebuild(Request $request): Response
    {
        /** @var CommodityProduct $product */
        $product = $this->getNewCommodity(Commodity::TYPE_PRODUCT);

        foreach ([
            [
                'name'          => 'region',
                'repository'    => Region::class,
                'setter'        => 'setRegion',
            ],
            [
                'name'          => 'district',
                'repository'    => District::class,
                'setter'        => 'setDistrict',
            ],
            [
                'name'          => 'locality',
                'repository'    => Locality::class,
                'setter'        => 'setLocality',
            ],
        ] as $fieldData) {
            $value  = (int) $request->get($fieldData['name']);
            $setter = $fieldData['setter'];
            $item   = $value > 0
                ? $this->getDoctrine()->getRepository($fieldData['repository'])->find($value)
                : null;
            $product->$setter($item);
        }

        return $this->render("profile/market/myCommodities/{$product->getCommodityType()}/localitiesFields.html.twig", [
            'form' => $this->createCommodityForm($product)->createView(),
        ]);
    }
    /**
     * @Route(
     *     "/profile/market/kit/form-preview-rebuild",
     *     name     = "market_profile_commodity_kit_preview_rebuild",
     *     methods  = "POST"
     * )
     */
    public function kitPreviewAjaxRebuild(Request $request): Response
    {
        $commodity  = $this->getNewCommodity(Commodity::TYPE_KIT);
        $form       = $this->createCommodityForm($commodity);

        try {
            $form->handleRequest($request);
        } catch (Throwable $exception) {
            return new JsonResponse([
                'success' => false,
            ]);
        }

        return new JsonResponse([
            'success'   => true,
            'minimized' => $this
                ->render("profile/market/myCommodities/{$commodity->getCommodityType()}/preview/minimized.html.twig", [
                    'form' => $form->createView(),
                ])
                ->getContent(),
            'full'      => $this
                ->render("profile/market/myCommodities/{$commodity->getCommodityType()}/preview/full.html.twig", [
                    'form' => $form->createView(),
                ])
                ->getContent(),
        ]);
    }
    /**
     * @Route(
     *     "/profile/market/kit/commodities-all",
     *     name     = "market_profile_commodity_kit_all_commodities_rebuild",
     *     methods  = "POST"
     * )
     */
    public function kitAllCommoditiesAjaxRebuild(Request $request): Response
    {
        $appliedFilter = $this->parseKitCommoditiesFilter($request);

        return new JsonResponse([
            'itemsList' => $this
                ->render('market/commodity/items.html.twig', [
                    'items'             => $this->getKitAllCommodities($appliedFilter),
                    'currentPage'       => $appliedFilter['all']['page'],
                    'paginationName'    => 'commoditiesSelector[all][page]',
                    'actions'           => [],
                    'mainAction'        => 'kitCreatingAdd',
                ])
                ->getContent(),
        ]);
    }
    /**
     * @Route(
     *     "/profile/market/kit/users",
     *     name     = "market_profile_commodity_kit_users_rebuild",
     *     methods  = "POST"
     * )
     */
    public function kitUsersAjaxRebuild(Request $request): Response
    {
        $appliedFilter = $this->parseKitCommoditiesFilter($request);

        return new JsonResponse([
            'itemsList' => $this
                ->render('market/user/items.html.twig', [
                    'items'             => $this->getKitCommoditiesUsers($appliedFilter),
                    'currentPage'       => $appliedFilter['users']['page'],
                    'paginationName'    => 'commoditiesSelector[users][page]',
                    'actions'           => [],
                    'mainAction'        => 'kitCreatingAdd',
                ])
                ->getContent(),
        ]);
    }
    /**
     * @Route(
     *     "/profile/market/kit/user-commodities",
     *     name     = "market_profile_commodity_kit_user_commodities_rebuild",
     *     methods  = "POST"
     * )
     */
    public function kitUserCommoditiesAjaxRebuild(Request $request): Response
    {
        $appliedFilter = $this->parseKitCommoditiesFilter($request);

        return new JsonResponse([
            'itemsList' => $this
                ->render('profile/market/myCommodities/kit/commodities/userCommoditiesItems.html.twig', [
                    'selectedUser'      => $this
                        ->getDoctrine()
                        ->getRepository(User::class)
                        ->find($appliedFilter['selectedUser']),
                    'commodities'       => $this->getKitUserCommodities($appliedFilter),
                    'currentPage'       => $appliedFilter['userCommodities']['page'],
                    'paginationName'    => 'commoditiesSelector[userCommodities][page]',
                    'actions'           => [],
                    'mainAction'        => 'kitCreatingAdd',
                ])
                ->getContent(),
        ]);
    }
    /**
     * @Route(
     *     "/profile/market/kit/commodities-own",
     *     name     = "market_profile_commodity_kit_own_commodities_rebuild",
     *     methods  = "POST"
     * )
     */
    public function kitOwnCommoditiesAjaxRebuild(Request $request): Response
    {
        $appliedFilter = $this->parseKitCommoditiesFilter($request);

        return new JsonResponse([
            'itemsList' => $this
                ->render('market/commodity/items.html.twig', [
                    'items'             => $this->getKitOwnCommodities($appliedFilter),
                    'currentPage'       => $appliedFilter['mine']['page'],
                    'paginationName'    => 'commoditiesSelector[mine][page]',
                    'actions'           => [],
                    'mainAction'        => 'kitCreatingAdd',
                ])
                ->getContent(),
        ]);
    }
    /**
     * @Route(
     *     "/profile/market/commodity/{commodityType}/{id}/activate",
     *     name     = "market_profile_commodity_activate",
     *     methods  = "POST"
     * )
     */
    public function commodityActivationToggle(string $commodityType, int $id): Response
    {
        /** @var User|null $currentUser */
        $currentUser    = $this->getUser();
        $commodity      = $this->getCommodityById($commodityType, $id);

        if (!$commodity) {
            return new JsonResponse([
                'success'   => false,
                'message'   => $this->translator->trans('market.profile.myCommodities.editAccessDenied'),
            ]);
        }

        try {
            $this->commodityActivityManager->checkCommodityIsPublished($commodity);
            $commodityIsActive  = true;
        } catch (Throwable $exception) {
            $commodityIsActive  = false;
        }

        try {
            $commodityIsActive
                ? $this->commodityActivityManager->checkCommodityCanBeDeactivated($commodity, $currentUser)
                : $this->commodityActivityManager->checkCommodityCanBeActivated($commodity, $currentUser);
        } catch (PublicationsCountControlException $exception) {
            return new JsonResponse([
                'success'   => false,
                'message'   => $this->translator->trans('market.profile.myCommodities.publicationsCountLeftNoMore'),
            ]);
        } catch (Throwable $exception) {
            return new JsonResponse([
                'success'   => false,
                'message'   => $this->translator->trans('market.profile.myCommodities.editAccessDenied'),
            ]);
        }

        if ($commodityIsActive) {
            $event = new CommodityDeactivationEvent($currentUser, $commodity);
        } else {
            $event = new CommodityActivationEvent();
            $event->setCommodity($commodity);
        }

        $this->eventDispatcher->dispatch($event);

        return new JsonResponse([
            'success'   => true,
            'item'      => $this
                ->render("market/commodity/{$commodity->getCommodityType()}/itemTablet.html.twig", [
                    'item'          => $commodity,
                    'actions'       => self::MY_COMMODITIES_ACTIONS,
                    'mainAction'    => self::MY_COMMODITIES_ACTION_MAIN,
                ])
                ->getContent(),
        ]);
    }
    /**
     * @Route(
     *     "/profile/market/kit/{id}/leaving",
     *     name     = "market_profile_kit_leaving",
     *     methods  = "POST"
     * )
     */
    public function kitLeavingToggle(int $id): Response
    {
        /**
         * @var User|null           $currentUser
         * @var CommodityKit|null   $kit
         */
        $currentUser    = $this->getUser();
        $kit            = $this->getCommodityById(Commodity::TYPE_KIT, $id);

        if (!$kit) {
            return new JsonResponse([
                'success'   => false,
                'message'   => $this->translator->trans('market.profile.myCommodities.editAccessDenied'),
            ]);
        }

        try {
            $this->commodityActivityManager->checkCommodityIsPublished($kit);
        } catch (Throwable $exception) {
            return new JsonResponse([
                'success'   => false,
                'message'   => $this->translator->trans('market.profile.myCommodities.editAccessDenied'),
            ]);
        }

        try {
            $this->commodityActivityManager->checkKitCanBeLeft($kit, $currentUser);
        } catch (Throwable $exception) {
            return new JsonResponse([
                'success'   => false,
                'message'   => $this->translator->trans('market.profile.myCommodities.editAccessDenied'),
            ]);
        }

        $event = new CommodityKitDeactivationByCoAuthorEvent();
        $event->setCommodity($kit);
        $event->setDeactivationInitiator($currentUser);
        $this->eventDispatcher->dispatch($event);

        return new JsonResponse([
            'success' => true,
        ]);
    }
    /**
     * Render my commodities.
     *
     * @param   Request $request            Request.
     * @param   string  $commodityType      Commodity type.
     *
     * @return  Response                    Response.
     */
    private function renderMyCommodities(Request $request, string $commodityType): Response
    {
        /** @var User|null $currentUser */
        $currentUser    = $this->getUser();
        $appliedFilter  = $this->parseFilter($request, $commodityType);
        $requiredRoles  = $this->getCommodityTypeRequiredRoles($commodityType);

        foreach ($requiredRoles as $role) {
            if (in_array($role, $currentUser->getRoles())) {
                return $this->render('profile/market/myCommodities/index.html.twig', [
                    'commodityType'         => $commodityType,
                    'items'                 => $this->getMyCommodities($appliedFilter, $commodityType),
                    'filter'                => $appliedFilter,
                    'availableSortValues'   => $this->getCommodityAvailableSortValues($commodityType),
                    'itemActions'           => self::MY_COMMODITIES_ACTIONS,
                    'mainAction'            => self::MY_COMMODITIES_ACTION_MAIN,
                    'publicationsCountLeft' => $this
                        ->userPublicationsManager
                        ->getPublicationsCountLeft($currentUser),
                ]);
            }
        }

        return $this->render('profile/market/myCommodities/roleRequired.html.twig', [
            'role' => $requiredRoles[0],
        ]);
    }
    /**
     * Render commodities (AJAX rebuild request).
     *
     * @param   Request $request            Request.
     * @param   string  $commodityType      Commodity type.
     *
     * @return  Response                    Response.
     */
    private function renderMyCommoditiesAjaxRebuild(Request $request, string $commodityType): Response
    {
        $appliedFilter      = $this->parseFilter($request, $commodityType);
        $listPageRouteName  = "market_profile_my_commodities_$commodityType";

        return new JsonResponse([
            'url'       => $this->generateUrl($listPageRouteName, $appliedFilter),
            'itemsList' => $this
                ->render('market/commodity/items.html.twig', [
                    'items'             => $this->getMyCommodities($appliedFilter, $commodityType),
                    'currentPage'       => $appliedFilter['page'],
                    'paginationName'    => 'page',
                    'actions'           => self::MY_COMMODITIES_ACTIONS,
                    'mainAction'        => self::MY_COMMODITIES_ACTION_MAIN,
                ])
                ->getContent(),
        ]);
    }
    /**
     * Render commodity form page.
     *
     * @param   Request     $request        Request.
     * @param   string      $commodityType  Commodity type.
     * @param   int|null    $commodityId    Commodity ID.
     *
     * @return  Response                    Response.
     */
    private function renderCommodityForm(
        Request $request,
        string  $commodityType,
        ?int    $commodityId = null
    ): Response {
        $isNewCommodity = is_null($commodityId);
        $commodity      = !$isNewCommodity
            ? $this->getCommodityById($commodityType, $commodityId)
            : $this->getNewCommodity($commodityType);

        $redirect = $this->checkCommodityFormAccess($commodity, $isNewCommodity);
        if ($redirect) {
            return $redirect;
        }

        $form = $this->createCommodityForm($commodity);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->saveCommodity($commodity);

            if ($isNewCommodity) {
                $createdCommodityLink = $commodityType === Commodity::TYPE_KIT
                    ? $this->generateUrl("market_profile_my_commodities_{$commodityType}_edit", [
                        'id' => $commodity->getId(),
                    ])
                    : $this->generateUrl("{$commodityType}_detail", [
                        'id' => $commodity->getId(),
                    ]);

                $this->addFlash('commodityCreated', $createdCommodityLink);
            }

            return $this->redirectToRoute("market_profile_my_commodities_{$commodity->getCommodityType()}");
        }

        return $this->render(
            "profile/market/myCommodities/{$commodity->getCommodityType()}/form.html.twig",
            array_merge(
                $this->getCommodityFormAdditionalData($request, $commodity),
                [
                    'form'      => $form->createView(),
                    'editable'  => $this->getCommodityFullyEditableState($commodity),
                ]
            ));
    }
    /**
     * Check commodity form access.
     *
     * Run different checks, get redirect if necessary.
     *
     * @param   Commodity|null  $commodity  Commodity if any.
     * @param   bool            $isNew      Is new commodity mark.
     *
     * @return  Response|null               Redirect if necessary.
     */
    private function checkCommodityFormAccess(?Commodity $commodity, bool $isNew): ?Response
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();

        if (!$isNew && !$commodity) {
            throw new NotFoundHttpException('commodity was not found');
        }
        if (!$isNew && $commodity->getUser() !== $currentUser) {
            throw new AccessDeniedHttpException('you are not a commodity owner');
        }
        if (
            $isNew &&
            $this->userPublicationsManager->getPublicationsCountLeft($currentUser) === 0
        ) {
            return $this->redirectToRoute('market_profile_my_commodities_'. $commodity->getCommodityType());
        }

        $requiredRoles      = $this->getCommodityTypeRequiredRoles($commodity->getCommodityType());
        $hasRequiredRole    = false;

        foreach ($requiredRoles as $role) {
            if (in_array($role, $currentUser->getRoles())) {
                $hasRequiredRole = true;
                break;
            }
        }

        if (!$hasRequiredRole) {
            return $this->render('profile/market/myCommodities/roleRequired.html.twig', [
                'role' => $requiredRoles[0],
            ]);
        }

        return null;
    }
    /**
     * Check if commodity fully editable.
     *
     * @param   Commodity $commodity        Commodity
     *
     * @return  bool                        Commodity is fully editable.
     */
    private function getCommodityFullyEditableState(Commodity $commodity): bool
    {
        if (!$commodity->getId()) {
            return true;
        }

        return $commodity->getActiveTo() < new DateTime('now');
    }
    /**
     * Run commodity saving process.
     *
     * @param   Commodity $commodity        Commodity.
     *
     * @return  void
     */
    private function saveCommodity(Commodity $commodity): void
    {
        $event = $commodity->getId()
            ? new CommodityUpdateEvent()
            : new CommodityCreateEvent();
        $event->setCommodity($commodity);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($commodity);
        $entityManager->flush();
        $this->eventDispatcher->dispatch($event);
    }
    /**
     * Parse filter from request.
     *
     * @param   Request $request            Request.
     * @param   string  $commodityType      Commodity type.
     *
     * @return  array                       Parsed filter.
     */
    private function parseFilter(Request $request, string $commodityType): array
    {
        $requestData                = $request->getMethod() === 'POST'
            ? $request->request->all()
            : $request->query->all();
        $basicFilter                = [];
        $commodityFilter            = [];

        $searchValue                = (string) ($requestData['search'] ?? '');
        $basicFilter['search']      = strlen($searchValue) > 0 ? $searchValue : null;

        $sortAvailableValues        = $this->getCommodityAvailableSortValues($commodityType);
        $sortValueIncome            = $requestData['sortField'] ?? null;
        $basicFilter['sortField']   = in_array($sortValueIncome, $sortAvailableValues)
            ? $sortValueIncome
            : $sortAvailableValues[0] ?? '';

        $pageValueIncome            = (int) ($requestData['page'] ?? 0);
        $basicFilter['page']        = $pageValueIncome > 0 ? $pageValueIncome : 1;

        switch ($commodityType) {
            case Commodity::TYPE_PRODUCT:
                $productsTypesIncome            = (array) ($requestData['productType'] ?? []);
                $commodityFilter['productType'] = array_filter($productsTypesIncome, function($value) {
                    return in_array($value, CommodityProduct::getAvailableTypes());
                });

                $activityIncome                 = (array) ($requestData['activity'] ?? []);
                $commodityFilter['activity']    = array_map(function($value): bool {
                    return (bool) $value;
                }, $activityIncome);
                break;
            case Commodity::TYPE_SERVICE:
            case Commodity::TYPE_KIT:
                $activityIncome                 = (array) ($requestData['activity'] ?? []);
                $commodityFilter['activity']    = array_map(function($value): bool {
                    return (bool) $value;
                }, $activityIncome);
                break;
            default:
        }

        return array_merge(
            $requestData,
            $basicFilter,
            $commodityFilter
        );
    }
    /**
     * Parse kit commodities filter from request.
     *
     * @param   Request $request            Request.
     *
     * @return  array                       Parsed filter.
     */
    private function parseKitCommoditiesFilter(Request $request): array
    {
        $requestData            = $request->getMethod() === 'POST'
            ? $request->request->all()
            : $request->query->all();

        $normalizePageValue     = function($value): int {
            $valueInt = (int) $value;
            return $valueInt > 0 ? $valueInt : 1;
        };
        $selectedCommodities    = (array) ($requestData['selectedCommodities'] ?? []);
        $selectedCommodities    = array_map(function(string $value): int {
            return (int) $value;
        }, $selectedCommodities);

        return [
            'all'                   => [
                'search'    => $requestData['commoditiesSelector']['all']['search'] ?? null,
                'page'      => $normalizePageValue(
                    $requestData['commoditiesSelector']['all']['page'] ?? null
                ),
            ],
            'users'                 => [
                'search'    => $requestData['commoditiesSelector']['users']['search'] ?? null,
                'page'      => $normalizePageValue(
                    $requestData['commoditiesSelector']['users']['page'] ?? null
                ),
            ],
            'userCommodities'       => [
                'page'      => $normalizePageValue(
                    $requestData['commoditiesSelector']['userCommodities']['page'] ?? null
                ),
            ],
            'mine'                  => [
                'search'    => $requestData['commoditiesSelector']['mine']['search'] ?? null,
                'page'      => $normalizePageValue(
                    $requestData['commoditiesSelector']['mine']['page'] ?? null
                ),
            ],
            'selectedCommodities'   => $selectedCommodities,
            'selectedUser'          => (int) ($requestData['selectedUser'] ?? 0),
        ];
    }
    /**
     * Get repository for given commodity type.
     *
     * @param   string $commodityType       Commodity type.
     *
     * @return  ObjectRepository            Repository.
     */
    private function getCommodityRepository(string $commodityType): ObjectRepository
    {
        $entityManager = $this->getDoctrine()->getManager();

        switch ($commodityType) {
            case Commodity::TYPE_PRODUCT:
                return $entityManager->getRepository(CommodityProduct::class);
            case Commodity::TYPE_SERVICE:
                return $entityManager->getRepository(CommodityService::class);
            case Commodity::TYPE_KIT:
                return $entityManager->getRepository(CommodityKit::class);
            default:
                throw new BadRequestHttpException();
        }
    }
    /**
     * Get commodity available sort values.
     *
     * @param   string $commodityType       Commodity type.
     *
     * @return  array                       Available sort values.
     */
    private function getCommodityAvailableSortValues(string $commodityType): array
    {
        switch ($commodityType) {
            case Commodity::TYPE_PRODUCT:
                return CommodityProductRepository::getListFilterAvailableSortValues();
            case Commodity::TYPE_SERVICE:
                return CommodityServiceRepository::getListFilterAvailableSortValues();
            case Commodity::TYPE_KIT:
                return CommodityKitRepository::getListFilterAvailableSortValues();
            default:
                return [];
        }
    }
    /**
     * Get my commodities.
     *
     * @param   array   $appliedFilter      Filter.
     * @param   string  $commodityType      Commodity type.
     *
     * @return  PaginationInterface         Items.
     */
    private function getMyCommodities(
        array   $appliedFilter,
        string  $commodityType
    ): PaginationInterface {
        /** @var User|null $currentUser */
        $currentUser    = $this->getUser();
        $basicFilter    = [
            'search'        => $appliedFilter['search'],
            'activity'      => count($appliedFilter['activity']) > 0
                ? $appliedFilter['activity']
                : [true, false],
        ];

        switch ($commodityType) {
            case Commodity::TYPE_PRODUCT:
                $commodityFilter = [
                    'type'  => $appliedFilter['productType'],
                    'user'  => $currentUser->getId(),
                ];
                break;
            case Commodity::TYPE_SERVICE:
                $commodityFilter = [
                    'user' => $currentUser->getId(),
                ];
                break;
            case Commodity::TYPE_KIT:
                $commodityFilter = [
                    'participant' => $currentUser->getId(),
                ];
                break;
            default:
                $commodityFilter = [];
        }

        $itemsQuery = $this
            ->getCommodityRepository($commodityType)
            ->listFilter(
                $currentUser,
                $appliedFilter['sortField'],
                array_merge($basicFilter, $commodityFilter)
            );

        return $this->paginate(
            $itemsQuery,
            $appliedFilter['page'],
            self::MY_COMMODITIES_PAGE_SIZE
        );
    }
    /**
     * Try to find commodity by ID.
     *
     * @param   string  $commodityType      Commodity type.
     * @param   int     $commodityId        Commodity ID.
     *
     * @return  Commodity|null              Commodity, if any.
     */
    private function getCommodityById(string $commodityType, int $commodityId): ?Commodity
    {
        /**
         * @var User|null       $currentUser
         * @var QueryBuilder    $queryBuilder
         */
        $currentUser    = $this->getUser();
        $queryBuilder   = $this
            ->getCommodityRepository($commodityType)
            ->listFilter(
                $currentUser,
                null,
                [
                    'id'        => $commodityId,
                    'activity'  => [true, false],
                ]
            );

        return $queryBuilder->getQuery()->getResult()[0] ?? null;
    }
    /**
     * Get new commodity.
     *
     * @param   string $commodityType       Commodity type.
     *
     * @return  Commodity                   New commodity.
     */
    private function getNewCommodity(string $commodityType): Commodity
    {
        $user = $this->getUser();
        switch ($commodityType) {
            case Commodity::TYPE_PRODUCT:
                return new CommodityProduct($user);
            case Commodity::TYPE_SERVICE:
                return new CommodityService($user);
            case Commodity::TYPE_KIT:
                return new CommodityKit();
            default:
                throw new BadRequestHttpException();
        }
    }
    /**
     * Get commodity form.
     *
     * @param   Commodity $commodity        Commodity.
     *
     * @return  FormInterface               Form.
     */
    private function createCommodityForm(Commodity $commodity): FormInterface
    {
        switch ($commodity->getCommodityType()) {
            case Commodity::TYPE_PRODUCT:
                return $this->createForm(ProductFormType::class, $commodity);
            case Commodity::TYPE_SERVICE:
                return $this->createForm(ServiceFormType::class, $commodity);
            case Commodity::TYPE_KIT:
                return $this->createForm(KitFormType::class, $commodity);
            default:
                throw new BadRequestHttpException();
        }
    }
    /**
     * Get commodity form additional data for output.
     *
     * @param   Request     $request        Request.
     * @param   Commodity   $commodity      Commodity.
     *
     * @return  array                       Additional data.
     */
    private function getCommodityFormAdditionalData(Request $request, Commodity $commodity): array
    {
        $result = [];

        if ($commodity->getCommodityType() === Commodity::TYPE_KIT) {
            /** @var CommodityKit $commodity */
            $appliedFilter = $this->parseKitCommoditiesFilter($request);

            foreach ($commodity->getCommodities() as $kitCommodity) {
                $appliedFilter['selectedCommodities'][] = $kitCommodity->getId();
            }

            $result['commodities'] = [
                'all'       => $this->getKitAllCommodities($appliedFilter),
                'sellers'   => $this->getKitCommoditiesUsers($appliedFilter),
                'own'       => $this->getKitOwnCommodities($appliedFilter),
            ];
        }

        return $result;
    }
    /**
     * Get kit commodities (ALL).
     *
     * @param   array   $appliedFilter      Filter.
     *
     * @return  PaginationInterface         Items.
     */
    private function getKitAllCommodities(array $appliedFilter): PaginationInterface
    {
        /** @var User|null $currentUser */
        $currentUser    = $this->getUser();
        $itemsQuery     = $this
            ->getDoctrine()
            ->getRepository(Commodity::class)
            ->listFilter(
                $currentUser,
                'id',
                [
                    'commodityType' => [
                        Commodity::TYPE_PRODUCT,
                        Commodity::TYPE_SERVICE,
                    ],
                    'type'          => CommodityProduct::TYPE_SELL,
                    'search'        => $appliedFilter['all']['search'],
                    '!id'           => $appliedFilter['selectedCommodities'],
                ]
            );

        return $this->paginate(
            $itemsQuery,
            $appliedFilter['all']['page'],
            self::KIT_COMMODITIES_PAGE_SIZE
        );
    }
    /**
     * Get kit commodities (USERS).
     *
     * @param   array $appliedFilter        Filter.
     *
     * @return  PaginationInterface         Items.
     */
    private function getKitCommoditiesUsers(array $appliedFilter): PaginationInterface
    {
        /** @var User|null $currentUser */
        $currentUser    = $this->getUser();
        $itemsQuery     = $this
            ->getDoctrine()
            ->getRepository(User::class)
            ->marketListFilter(
                $currentUser,
                'id',
                [
                    'search'    => $appliedFilter['users']['search'],
                    '!id'       => $currentUser->getId(),
                ]
            );

        return $this->paginate(
            $itemsQuery,
            $appliedFilter['users']['page'],
            self::KIT_COMMODITIES_PAGE_SIZE
        );
    }
    /**
     * Get kit commodities (USER COMMODITIES).
     *
     * @param   array $appliedFilter        Filter.
     *
     * @return  PaginationInterface         Items.
     */
    private function getKitUserCommodities(array $appliedFilter): PaginationInterface
    {
        /** @var User|null $currentUser */
        $currentUser    = $this->getUser();
        $itemsQuery     = $this
            ->getDoctrine()
            ->getRepository(Commodity::class)
            ->listFilter(
                $currentUser,
                'id',
                [
                    'commodityType' => [
                        Commodity::TYPE_PRODUCT,
                        Commodity::TYPE_SERVICE,
                    ],
                    'type'          => CommodityProduct::TYPE_SELL,
                    'user'          => $appliedFilter['selectedUser'],
                    '!id'           => $appliedFilter['selectedCommodities'],
                ]
            );

        return $this->paginate(
            $itemsQuery,
            $appliedFilter['userCommodities']['page'],
            self::KIT_COMMODITIES_PAGE_SIZE
        );
    }
    /**
     * Get kit commodities (OWN).
     *
     * @param   array $appliedFilter        Filter.
     *
     * @return  PaginationInterface         Items.
     */
    private function getKitOwnCommodities(array $appliedFilter): PaginationInterface
    {
        /** @var User|null $currentUser */
        $currentUser    = $this->getUser();
        $itemsQuery     = $this
            ->getDoctrine()
            ->getRepository(Commodity::class)
            ->listFilter(
                $currentUser,
                'id',
                [
                    'commodityType' => [
                        Commodity::TYPE_PRODUCT,
                        Commodity::TYPE_SERVICE,
                    ],
                    'type'          => CommodityProduct::TYPE_SELL,
                    'search'        => $appliedFilter['mine']['search'],
                    'user'          => $currentUser->getId(),
                    '!id'           => $appliedFilter['selectedCommodities'],
                ]
            );

        return $this->paginate(
            $itemsQuery,
            $appliedFilter['mine']['page'],
            self::KIT_COMMODITIES_PAGE_SIZE
        );
    }
    /**
     * Get required roles set for given commodity type.
     *
     * @param   string $commodityType       Commodity type.
     *
     * @return  string[]                    Roles set.
     */
    private function getCommodityTypeRequiredRoles(string $commodityType): array
    {
        switch ($commodityType) {
            case Commodity::TYPE_PRODUCT:
                return CommodityProduct::REQUIRED_USER_ROLES;
            case Commodity::TYPE_SERVICE:
                return CommodityService::REQUIRED_USER_ROLES;
            case Commodity::TYPE_KIT:
                return CommodityKit::REQUIRED_USER_ROLES;
            default:
                throw new BadRequestHttpException();
        }
    }
}
