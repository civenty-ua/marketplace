<?php
declare(strict_types = 1);

namespace App\Controller\Profile\Market;

use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    JsonResponse,
};
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\Pagination\PaginationInterface;
use App\Repository\Market\{
    CommodityProductRepository,
    CommodityServiceRepository,
    CommodityKitRepository,
};
use App\Entity\{
    User,
    Market\Commodity,
    Market\CommodityProduct,
    Market\CommodityService,
    Market\CommodityKit,
};
/**
 * @package App\Controller\Profile
 */
class FavoritesController extends ProfileMarketController
{
    private const FAVORITES_PAGE_SIZE               = 24;
    private const FAVORITES_COMMODITIES_ACTION_MAIN = 'view';
    private const FAVORITES_COMMODITIES_ACTIONS     = [
        'view',
        'removeFromFavorite',
    ];
    private const FAVORITES_USERS_ACTION_MAIN       = 'view';
    private const FAVORITES_USERS_ACTIONS           = [
        'view',
        'removeFromFavorite',
    ];
    /**
     * @Route("/profile/market/favorites-products", name="market_profile_favorites_products")
     */
    public function favoritesProducts(Request $request): Response
    {
        return $this->renderFavorites($request, Commodity::TYPE_PRODUCT);
    }
    /**
     * @Route(
     *     "/profile/market/favorites-products-list-rebuild",
     *     name     = "market_profile_favorites_products_list_rebuild",
     *     methods  = "POST"
     * )
     */
    public function favoritesProductsAjaxRebuild(Request $request): Response
    {
        return $this->renderFavoritesAjaxRebuild(
            $request,
            'market_profile_favorites_products',
            Commodity::TYPE_PRODUCT
        );
    }
    /**
     * @Route("/profile/market/favorites-services", name="market_profile_favorites_services")
     */
    public function favoritesServices(Request $request): Response
    {
        return $this->renderFavorites($request, Commodity::TYPE_SERVICE);
    }
    /**
     * @Route(
     *     "/profile/market/favorites-services-list-rebuild",
     *     name     = "market_profile_favorites_services_list_rebuild",
     *     methods  = "POST"
     * )
     */
    public function favoritesServicesAjaxRebuild(Request $request): Response
    {
        return $this->renderFavoritesAjaxRebuild(
            $request,
            'market_profile_favorites_services',
            Commodity::TYPE_SERVICE
        );
    }
    /**
     * @Route("/profile/market/favorites-kits", name="market_profile_favorites_kits")
     */
    public function favoritesKits(Request $request): Response
    {
        return $this->renderFavorites($request, Commodity::TYPE_KIT);
    }
    /**
     * @Route(
     *     "/profile/market/favorites-kits-list-rebuild",
     *     name     = "market_profile_favorites_kits_list_rebuild",
     *     methods  = "POST"
     * )
     */
    public function favoritesKitsAjaxRebuild(Request $request): Response
    {
        return $this->renderFavoritesAjaxRebuild(
            $request,
            'market_profile_favorites_kits',
            Commodity::TYPE_KIT
        );
    }
    /**
     * @Route("/profile/market/favorites-users", name="market_profile_favorites_users")
     */
    public function favoritesUsers(Request $request): Response
    {
        return $this->renderFavorites($request, 'user');
    }
    /**
     * @Route(
     *     "/profile/market/favorites-users-list-rebuild",
     *     name     = "market_profile_favorites_users_list_rebuild",
     *     methods  = "POST"
     * )
     */
    public function favoritesUsersAjaxRebuild(Request $request): Response
    {
        return $this->renderFavoritesAjaxRebuild(
            $request,
            'market_profile_favorites_users',
            'user'
        );
    }
    /**
     * Render favorites.
     *
     * @param   Request $request            Request.
     * @param   string  $favoriteType       Favorite type.
     *
     * @return  Response                    Response.
     */
    private function renderFavorites(Request $request, string $favoriteType): Response
    {
        $itemsCount             = $this->getFavoritesItemsCount();
        $favoriteTypeProcessed  = $favoriteType;

        if ($itemsCount[$favoriteType] === 0) {
            foreach ($itemsCount as $index => $value) {
                if ($value > 0) {
                    $favoriteTypeProcessed = $index;
                    break;
                }
            }
        }

        $appliedFilter = $this->parseFilter($request, $favoriteTypeProcessed);

        return $this->render('profile/market/favorites/index.html.twig', [
            'favoriteType'          => $favoriteTypeProcessed,
            'itemsCount'            => $itemsCount,
            'items'                 => $this->getFavorites($appliedFilter, $favoriteTypeProcessed),
            'filter'                => $appliedFilter,
            'availableSortValues'   => $this->getFavoritesAvailableSortValues($favoriteTypeProcessed),
            'itemActions'           => $favoriteType === 'user'
                ? self::FAVORITES_USERS_ACTIONS
                : self::FAVORITES_COMMODITIES_ACTIONS,
            'mainAction'            => $favoriteType === 'user'
                ? self::FAVORITES_USERS_ACTION_MAIN
                : self::FAVORITES_COMMODITIES_ACTION_MAIN,
        ]);
    }
    /**
     * Render favorite (AJAX rebuild request).
     *
     * @param   Request $request            Request.
     * @param   string  $listPageUrl        List page URL.
     * @param   string  $favoriteType       Favorite type.
     *
     * @return  Response                    Response.
     */
    private function renderFavoritesAjaxRebuild(
        Request $request,
        string  $listPageUrl,
        string  $favoriteType
    ): Response {
        $appliedFilter      = $this->parseFilter($request, $favoriteType);
        $itemsTemplatePath  = '';

        switch ($favoriteType) {
            case Commodity::TYPE_PRODUCT:
            case Commodity::TYPE_SERVICE:
            case Commodity::TYPE_KIT:
                $itemsTemplatePath = 'market/commodity/items.html.twig';
                break;
            case 'user':
                $itemsTemplatePath = 'market/user/items.html.twig';
                break;
            default:
        }

        return new JsonResponse([
            'url'       => $this->generateUrl($listPageUrl, $appliedFilter),
            'itemsList' => $this
                ->render($itemsTemplatePath, [
                    'items'             => $this->getFavorites($appliedFilter, $favoriteType),
                    'currentPage'       => $appliedFilter['page'],
                    'paginationName'    => 'page',
                    'actions'           => $favoriteType === 'user'
                        ? self::FAVORITES_USERS_ACTIONS
                        : self::FAVORITES_COMMODITIES_ACTIONS,
                    'mainAction'        => $favoriteType === 'user'
                        ? self::FAVORITES_USERS_ACTION_MAIN
                        : self::FAVORITES_COMMODITIES_ACTION_MAIN,
                ])
                ->getContent(),
        ]);
    }
    /**
     * Get user favorites items count.
     *
     * @return array                        Items count data.
     */
    private function getFavoritesItemsCount(): array
    {
        /** @var User|null $currentUser */
        $currentUser    = $this->getUser();
        $repositories   = [
            Commodity::TYPE_PRODUCT => CommodityProduct::class,
            Commodity::TYPE_SERVICE => CommodityService::class,
            Commodity::TYPE_KIT     => CommodityKit::class,
            'user'                  => User::class,
        ];
        $result         = [];

        foreach ($repositories as $index => $repository) {
            $result[$index] = $this
                ->getDoctrine()
                ->getRepository($repository)
                ->getTotalCount($currentUser, [
                    'inFavorite' => true,
                ]);
        }

        return $result;
    }
    /**
     * Get favorites available sort values.
     *
     * @param   string  $favoriteType       Favorite type.
     *
     * @return  array                       Available sort values.
     */
    private function getFavoritesAvailableSortValues(string $favoriteType): array
    {
        switch ($favoriteType) {
            case Commodity::TYPE_PRODUCT:
                return CommodityProductRepository::getListFilterAvailableSortValues();
            case Commodity::TYPE_SERVICE:
                return CommodityServiceRepository::getListFilterAvailableSortValues();
            case Commodity::TYPE_KIT:
                return CommodityKitRepository::getListFilterAvailableSortValues();
            case 'user':
            default:
                return [];
        }
    }
    /**
     * Parse filter from request.
     *
     * @param   Request $request            Request.
     * @param   string  $favoriteType       Favorite type.
     *
     * @return  array                       Parsed filter.
     */
    private function parseFilter(Request $request, string $favoriteType): array
    {
        $requestData                = $request->getMethod() === 'POST'
            ? $request->request->all()
            : $request->query->all();
        $basicFilter                = [];
        $favoritesFilter            = [];

        $sortAvailableValues        = $this->getFavoritesAvailableSortValues($favoriteType);
        $sortValueIncome            = $requestData['sortField'] ?? null;
        $basicFilter['sortField']   = in_array($sortValueIncome, $sortAvailableValues)
            ? $sortValueIncome
            : $sortAvailableValues[0] ?? '';

        $pageValueIncome            = (int) ($requestData['page'] ?? 0);
        $basicFilter['page']        = $pageValueIncome > 0 ? $pageValueIncome : 1;

        switch ($favoriteType) {
            case Commodity::TYPE_PRODUCT:
                $productsTypesIncome            = (array) ($requestData['productType'] ?? []);
                $favoritesFilter['productType'] = array_filter($productsTypesIncome, function($value) {
                    return in_array($value, CommodityProduct::getAvailableTypes());
                });
                break;
            case Commodity::TYPE_SERVICE:
            case Commodity::TYPE_KIT:
            case 'user':
            default:
        }

        return array_merge(
            $requestData,
            $basicFilter,
            $favoritesFilter
        );
    }
    /**
     * Get favorites items.
     *
     * @param   array   $appliedFilter      Filter.
     * @param   string  $favoriteType       Favorite type.
     *
     * @return  PaginationInterface         Items.
     */
    private function getFavorites(array $appliedFilter, string $favoriteType): PaginationInterface
    {
        /** @var User|null $currentUser */
        $currentUser    = $this->getUser();
        $repository     = null;
        $methodProvider = null;
        $favoriteFilter = [];

        switch ($favoriteType) {
            case Commodity::TYPE_PRODUCT:
                $repository     = CommodityProduct::class;
                $methodProvider = 'listFilter';
                $favoriteFilter = [
                    'type' => $appliedFilter['productType'],
                ];
                break;
            case Commodity::TYPE_SERVICE:
                $repository     = CommodityService::class;
                $methodProvider = 'listFilter';
                break;
            case Commodity::TYPE_KIT:
                $repository     = CommodityKit::class;
                $methodProvider = 'listFilter';
                break;
            case 'user':
                $repository     = User::class;
                $methodProvider = 'marketListFilter';
            default:
        }

        $itemsQuery = $this
            ->getDoctrine()
            ->getRepository($repository)
            ->$methodProvider(
                $currentUser,
                $appliedFilter['sortField'],
                array_merge($favoriteFilter, [
                    'inFavorite' => true,
                ])
            );

        return $this->paginate(
            $itemsQuery,
            $appliedFilter['page'],
            self::FAVORITES_PAGE_SIZE
        );
    }
}
