<?php
declare(strict_types = 1);

namespace App\Controller\Market;

use App\Helper\SeoHelper;
use App\Service\SeoService;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    JsonResponse,
};
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\Pagination\PaginationInterface;
use App\Repository\Market\CommodityKitRepository;
use App\Entity\{
    User,
    Market\Category,
    Market\Commodity,
    Market\CommodityProduct,
    Market\CommodityService,
    Market\CommodityKit,
    UserToUserRate,
};
use App\Entity\Market\Notification\BidOffer;
/**
 * Kits controller.
 *
 * @package App\Controller
 */
class KitController extends CommodityController
{
    /**
     * @Route("/marketplace/kits", name="kits_list")
     */
    public function list(Request $request, SeoService $seoService): Response
    {
        $optionsDescription = [];
        foreach ($this->options as $key => $value) {
            $optionsDescription[$key] = $value->getValue();
        }
        $appliedFilter  = $this->parseListFilter($request);
        $fieldsData     = $this->prepareFieldsData($appliedFilter);

        $seo = $seoService->setPage(SeoHelper::PAGE_MARKETPLACE_KITS)->getSeo();

        return $this->render('market/commodity/kit/list.html.twig', [
            'optionDescriptionUk' => $optionsDescription['market_proposals_description_uk'],
            'optionDescriptionEn' => $optionsDescription['market_proposals_description_en'],
            'seo'           => $seo,
            'fields'        => $fieldsData,
            'filter'        => $appliedFilter,
            'items'         => $this->prepareListItemsData(
                $appliedFilter,
                $fieldsData['filters']
            ),
            'itemActions'   => self::ITEM_ACTIONS,
            'mainAction'    => self::ITEM_ACTION_MAIN,
        ]);
    }
    /**
     * @Route(
     *     "/marketplace/kits/ajax/list-rebuild",
     *     name     = "kits_list_ajax_rebuild",
     *     methods  = "POST"
     * )
     */
    public function listAjaxRebuild(Request $request): Response
    {
        $appliedFilter  = $this->parseListFilter($request);
        $fieldsData     = $this->prepareFieldsData($appliedFilter);

        return new JsonResponse([
            'url'               => $this->generateUrl('kits_list', $appliedFilter),
            'filter'            => $this
                ->render('market/commodity/kit/filter.html.twig', [
                    'fields'            => $fieldsData,
                    'filter'            => $appliedFilter,
                ])
                ->getContent(),
            'appliedFiltersBar' => $this
                ->render('market/commodity/kit/listAppliedFilterBar.html.twig', [
                    'fields'    => $fieldsData,
                    'filter'    => $appliedFilter,
                ])
                ->getContent(),
            'itemsList'         => $this
                ->render('market/commodity/items.html.twig', [
                    'items'             => $this->prepareListItemsData($appliedFilter, $fieldsData['filters']),
                    'currentPage'       => $appliedFilter['page'],
                    'paginationName'    => 'page',
                    'actions'           => self::ITEM_ACTIONS,
                    'mainAction'        => self::ITEM_ACTION_MAIN,
                ])
                ->getContent(),
        ]);
    }
    /**
     * @Route("/marketplace/kits/{id}", name="kit_detail")
     */
    public function detail(string $id, SeoService $seoService): Response
    {
        $item = $this->getCommodityDetail($id);

        if (!$item) {
            throw new NotFoundHttpException();
        }

        $bidOfferExist =  $this
            ->getDoctrine()
            ->getRepository(BidOffer::class)
            ->findOneBy([
                'sender' => $this->getUser(),
                'receiver' => $item->getUser()
            ]) != null;
        $userAlreadyRated = $this
            ->getDoctrine()
            ->getRepository(UserToUserRate::class)
            ->findOneBy([
                'user' => $this->getUser(),
                'targetUser' => $item->getUser()
            ]) != null;

        $seo = $seoService
            ->setPage(SeoHelper::PAGE_MARKETPLACE_KIT)
            ->getSeo(['title' => $item->getTitle()])
        ;

        $this->fireCommodityRequestEvent($item);

        return $this->render('market/commodity/kit/itemDetail.html.twig', [
            'seo'           => $seoService->merge($seo, $item->getSeo()),
            'item'          => $item,
            'sameItems'     => [
                'items'         => $this->getCommoditySameItems($item),
                'link'          => $this->getSameItemsLink($item),
                'actions'       => self::ITEM_ACTIONS,
                'mainAction'    => self::ITEM_ACTION_MAIN,
            ],
            'sameSellers'   => [
                'items'         => $this->getCommoditySameSellers($item),
                'actions'       => self::USER_ACTIONS,
                'mainAction'    => self::USER_ACTION_MAIN,
            ],
            'userCanRate'   => $bidOfferExist && !$userAlreadyRated,
            'rate'          => $this
                ->getDoctrine()
                ->getRepository(UserToUserRate::class)
                ->getUserRateValue($item->getUser())
        ]);
    }
    /**
     * @inheritdoc
     */
    protected function getSortAvailableValues(): array
    {
        return CommodityKitRepository::getListFilterAvailableSortValues();
    }
    /**
     * @inheritdoc
     */
    protected function parseListFilter(Request $request): array
    {
        $requestData            = parent::parseListFilter($request);
        $filtersData            = (array) ($requestData['filters'] ?? []);
        $priceFrom              = (int) ($requestData['price'][0]   ?? 0);
        $priceTo                = (int) ($requestData['price'][1]   ?? 0);
        $filtersDataPrepared    = [];

        foreach ($filtersData as $filterData) {
            $commodityType  = (string)  ($filterData['commodityType']   ?? '');
            $category       = (int)     ($filterData['category']        ?? 0);
            $subCategory    = (int)     ($filterData['subCategory']     ?? 0);

            switch ($commodityType) {
                case Commodity::TYPE_PRODUCT:
                    $commoditySpecificFilterData = $this->parseListProductFilter($filterData);
                    break;
                case Commodity::TYPE_SERVICE:
                    $commoditySpecificFilterData = $this->parseListServiceFilter($filterData);
                    break;
                default:
                    continue 2;
            }

            $filtersDataPrepared[] = array_merge($commoditySpecificFilterData, [
                'commodityType'     => $commodityType,
                'filterIsClosed'    => (bool)   ($filterData['filterIsClosed']  ?? false),
                'category'          => $category > 0 ? $category : null,
                'subCategory'       => $category > 0 && $subCategory > 0 ? $subCategory : null,
                'attributes'        => (array)  ($filterData['attributes']      ?? []),
            ]);
        }

        return array_merge($requestData, [
            'filters'   => $filtersDataPrepared,
            'price'     => [
                $priceFrom  > 0 ? $priceFrom    : null,
                $priceTo    > 0 ? $priceTo      : null,
            ],
        ]);
    }
    /**
     * Parse product list filter.
     *
     * @param   array $filterData           Filter income data.
     *
     * @return  array                       Parsed filter.
     */
    protected function parseListProductFilter(array $filterData): array
    {
        return array_merge($filterData, [
            'organicOnly' => isset($filterData['organicOnly'])
                ? (bool) ($filterData['organicOnly'] ?? false)
                : null,
        ]);
    }
    /**
     * Parse service list filter.
     *
     * @param   array $filterData           Filter income data.
     *
     * @return  array                       Parsed filter.
     */
    protected function parseListServiceFilter(array $filterData): array
    {
        return $filterData;
    }
    /**
     * @inheritdoc
     */
    protected function prepareFieldsData(array &$appliedFilter): array
    {
        $filtersData            = [];
        $categoryAttributesAll  = [];

        foreach ($appliedFilter['filters'] as $index => $filterData) {
            $subCategories      = $filterData['category']
                ? $this->getCategoriesSet($filterData['commodityType'], $filterData['category'])
                : [];
            $categoryAttributes = [];

            if (!isset($subCategories[$filterData['subCategory']])) {
                $filterData['subCategory'] = $appliedFilter['filters'][$index]['subCategory'] = null;
            }

            if ($filterData['subCategory']) {
                $categoryAttributes = $this->getCategoryAttributesParameters($filterData['subCategory']);
            } elseif ($filterData['category'] && count($subCategories) === 0) {
                $categoryAttributes = $this->getCategoryAttributesParameters($filterData['category']);
            }

            $categoryAttributesAll  = array_merge($categoryAttributesAll, $categoryAttributes);
            $filtersData[]          = [
                'categories'    => $this->getCategoriesSet($filterData['commodityType']),
                'subCategories' => $subCategories,
                'attributes'    => $categoryAttributes,
            ];
        }

        return [
            'availableSortValues'   => $this->getSortAvailableValues(),
            'filters'               => $filtersData,
            'maxValues'             => [
                'price'                 => $this
                    ->getDoctrine()
                    ->getRepository(CommodityKit::class)
                    ->getMaxPrice(),
                'attributes'            => $this->getAttributesMaxValues($categoryAttributesAll),
            ],
        ];
    }
    /**
     * Prepare items data for output.
     *
     * @param   array   $appliedFilter  Filter.
     * @param   array   $filtersData    Filter fields data.
     *
     * @return  PaginationInterface     Items.
     */
    protected function prepareListItemsData(array $appliedFilter, array $filtersData): PaginationInterface
    {
        /** @var User|null $currentUser */
        $currentUser            = $this->getUser();
        $attributesParameters   = [];
        $appliedSubFilters      = [];

        foreach ($filtersData as $index => $filterData) {
            $attributesParameters   = array_merge($attributesParameters, $filterData['attributes']);
            $appliedSubFilter       = $appliedFilter['filters'][$index];

            if ($appliedSubFilter['subCategory']) {
                $appliedSubFilter['category'] = [$appliedSubFilter['subCategory']];
            } elseif ($appliedSubFilter['category']) {
                $appliedSubFilter['category'] = [$appliedSubFilter['category']];
                foreach ($filterData['subCategories'] as $subCategory) {
                    /** @var Category $subCategory */
                    $appliedSubFilter['category'][] = $subCategory->getId();
                }
            }

            $appliedSubFilters[] = $appliedSubFilter;
        }

        $itemsQuery = $this
            ->getDoctrine()
            ->getRepository(CommodityKit::class)
            ->listFilter(
                $currentUser,
                $appliedFilter['sortField'],
                [
                    'price'     => $appliedFilter['price'],
                    'search'    => $appliedFilter['search'],
                ],
                $appliedSubFilters,
                $attributesParameters
            );

        return $this->paginate(
            $itemsQuery,
            $appliedFilter['page'],
            self::PAGE_SIZE
        );
    }
    /**
     * @inheritdoc
     */
    protected function provideCommodityDetail(string $id): QueryBuilder
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();

        return $this
            ->getDoctrine()
            ->getRepository(CommodityKit::class)
            ->listFilter(
                $currentUser,
                null,
                ['slug' => $id],
            );
    }
    /**
     * @inheritdoc
     */
    protected function provideCommoditySameItems(Commodity $item): QueryBuilder
    {
        /**
         * @var User|null                           $currentUser
         * @var CommodityKit                        $item
         * @var CommodityProduct|CommodityService   $commodity
         */
        $currentUser        = $this->getUser();
        $commoditiesFilters = [];

        foreach ($item->getCommodities() as $commodity) {
            $commoditiesFilters[] = [
                'commodityType' => $commodity->getCommodityType(),
                'category'      => [$commodity->getCategory()->getId()],
            ];
        }

        return $this
            ->getDoctrine()
            ->getRepository(CommodityKit::class)
            ->listFilter(
                $currentUser,
                null,
                [
                    '!slug' => $item->getId(),
                ],
                $commoditiesFilters
            );
    }
    /**
     * @inheritdoc
     */
    protected function getSameItemsLink(Commodity $item): string
    {
        /** @var CommodityKit $item */
        $commoditiesFilters = [];

        foreach ($item->getCommodities() as $commodity) {
            $commodityFilter = [
                'commodityType' => $commodity->getCommodityType(),
            ];

            if ($commodity->getCategory()->getParent()) {
                $commodityFilter['category']    = $commodity->getCategory()->getParent()->getId();
                $commodityFilter['subCategory'] = $commodity->getCategory()->getId();
            } else {
                $commodityFilter['category']    = $commodity->getCategory()->getId();
            }

            $commoditiesFilters[$commodity->getCategory()->getId()] = $commodityFilter;
        }

        return $this->generateUrl('kits_list', [
            'filters' => $commoditiesFilters
        ]);
    }
    /**
     * @inheritdoc
     */
    protected function provideCommoditySameItemsForSameSellers(Commodity $item): QueryBuilder
    {
        return $this->provideCommoditySameItems($item);
    }
}
