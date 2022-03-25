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
use App\Repository\Market\CommodityProductRepository;
use App\Entity\{
    User,
    Market\Category,
    Market\CategoryAttributeParameters,
    Market\Commodity,
    Market\CommodityProduct,
    UserToUserRate,
};
use App\Entity\Market\Notification\BidOffer;
/**
 * Products controller.
 *
 * @package App\Controller
 */
class ProductController extends CommodityController
{
    /**
     * @Route("/marketplace/products", name="products_list")
     */
    public function list(Request $request, SeoService $seoService): Response
    {
        $optionsDescription = [];
        foreach ($this->options as $key => $value) {
            $optionsDescription[$key] = $value->getValue();
        }
        $appliedFilter  = $this->parseListFilter($request);
        $fieldsData     = $this->prepareFieldsData($appliedFilter);

        $metaRobots = null;
        foreach ($appliedFilter as $value) {
            if (!empty($value)) {
                $metaRobots = 'noindex, nofollow';
                break;
            }
        }
        return $this->render('market/commodity/product/list.html.twig', [
            'optionDescriptionUk' => $optionsDescription['market_goods_description_uk'],
            'optionDescriptionEn' => $optionsDescription['market_goods_description_en'],
            'metaRobots'    => $metaRobots,
            'seo'           => $seoService
                ->setPage(SeoHelper::PAGE_MARKETPLACE_PRODUCTS)
                ->getSeo(),
            'fields'        => $fieldsData,
            'filter'        => $appliedFilter,
            'items'         => $this->prepareListItemsData(
                $appliedFilter,
                $fieldsData['subCategories'],
                $fieldsData['attributes']
            ),
            'itemActions'   => self::ITEM_ACTIONS,
            'mainAction'    => self::ITEM_ACTION_MAIN,
        ]);
    }
    /**
     * @Route(
     *     "/marketplace/products/ajax/list-rebuild",
     *     name     = "products_list_ajax_rebuild",
     *     methods  = "POST"
     * )
     */
    public function listAjaxRebuild(Request $request): Response
    {
        $appliedFilter  = $this->parseListFilter($request);
        $fieldsData     = $this->prepareFieldsData($appliedFilter);

        return new JsonResponse([
            'url'               => $this->generateUrl('products_list', $appliedFilter),
            'filter'            => $this
                ->render('market/commodity/product/filter.html.twig', [
                    'fields'            => $fieldsData,
                    'filter'            => $appliedFilter,
                ])
                ->getContent(),
            'appliedFiltersBar' => $this
                ->render('market/commodity/product/listAppliedFilterBar.html.twig', [
                    'fields'            => $fieldsData,
                    'filter'            => $appliedFilter,
                ])
                ->getContent(),
            'itemsList'         => $this
                ->render('market/commodity/items.html.twig', [
                    'items'             => $this->prepareListItemsData(
                        $appliedFilter,
                        $fieldsData['subCategories'],
                        $fieldsData['attributes']
                    ),
                    'currentPage'       => $appliedFilter['page'],
                    'paginationName'    => 'page',
                    'actions'           => self::ITEM_ACTIONS,
                    'mainAction'        => self::ITEM_ACTION_MAIN,
                ])
                ->getContent(),
        ]);
    }
    /**
     * @Route("/marketplace/products/{id}", name="product_detail")
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
            ->setPage(SeoHelper::PAGE_MARKETPLACE_PRODUCT)
            ->getSeo(['title' => $item->getTitle()]);

        $this->fireCommodityRequestEvent($item);

        return $this->render('market/commodity/product/itemDetail.html.twig', [
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
            'sameCategory' => [
                'link'          => $this->getSameCategoryLink($item),
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
        return CommodityProductRepository::getListFilterAvailableSortValues();
    }
    /**
     * @inheritdoc
     */
    protected function parseListFilter(Request $request): array
    {
        $requestData                = parent::parseListFilter($request);

        $category                   = (int) ($requestData['category'] ?? 0);
        $requestData['category']    = $category > 0 ? $category : null;
        $subCategory                = (int) ($requestData['subCategory'] ?? 0);
        $requestData['subCategory'] = $subCategory > 0 && $requestData['category'] ? $subCategory : null;

        $requestData['attributes']  = (array) ($requestData['attributes'] ?? []);

        $priceFrom                  = (int) ($requestData['price'][0] ?? 0);
        $priceTo                    = (int) ($requestData['price'][1] ?? 0);
        $requestData['price']       = [
            $priceFrom  > 0 ? $priceFrom    : null,
            $priceTo    > 0 ? $priceTo      : null,
        ];

        $productsTypes              = (array)   ($requestData['productType']    ?? []);
        $requestData['productType'] = array_filter($productsTypes, function($value) {
            return in_array($value, CommodityProduct::getAvailableTypes());
        });
        $requestData['organicOnly'] = isset($requestData['organicOnly'])
            ? (bool) ($requestData['organicOnly'] ?? false)
            : null;

        return $requestData;
    }
    /**
     * @inheritdoc
     */
    protected function prepareFieldsData(array &$appliedFilter): array
    {
        $subCategories      = $appliedFilter['category']
            ? $this->getCategoriesSet(Commodity::TYPE_PRODUCT, $appliedFilter['category'])
            : [];
        $categoryAttributes = [];

        if (!isset($subCategories[$appliedFilter['subCategory']])) {
            $appliedFilter['subCategory'] = null;
        }

        if ($appliedFilter['subCategory']) {
            $categoryAttributes = $this->getCategoryAttributesParameters($appliedFilter['subCategory']);
        } elseif ($appliedFilter['category'] && count($subCategories) === 0) {
            $categoryAttributes = $this->getCategoryAttributesParameters($appliedFilter['category']);
        }

        return [
            'availableSortValues'   => $this->getSortAvailableValues(),
            'categories'            => $this->getCategoriesSet(Commodity::TYPE_PRODUCT),
            'subCategories'         => $subCategories,
            'attributes'            => $categoryAttributes,
            'maxValues'             => [
                'price'                 => $this
                    ->getDoctrine()
                    ->getRepository(CommodityProduct::class)
                    ->getMaxPrice(),
                'attributes'            => $this->getAttributesMaxValues($categoryAttributes),
            ],
        ];
    }
    /**
     * Prepare list items data for output.
     *
     * @param   array                           $appliedFilter          Filter.
     * @param   Category[]                      $subCategories          Subcategories list.
     * @param   CategoryAttributeParameters[]   $attributesParameters   Category attributes parameters.
     *
     * @return  PaginationInterface                                     Items.
     */
    protected function prepareListItemsData(
        array   $appliedFilter,
        array   $subCategories,
        array   $attributesParameters
    ): PaginationInterface {
        /** @var User|null $currentUser */
        $currentUser        = $this->getUser();
        $categoriesFilter   = [];

        if ($appliedFilter['subCategory']) {
            $categoriesFilter = [$appliedFilter['subCategory']];
        } elseif ($appliedFilter['category']) {
            $categoriesFilter = [$appliedFilter['category']];

            foreach ($subCategories as $subCategory) {
                $categoriesFilter[] = $subCategory->getId();
            }
        }

        $itemsQuery = $this
            ->getDoctrine()
            ->getRepository(CommodityProduct::class)
            ->listFilter(
                $currentUser,
                $appliedFilter['sortField'],
                [
                    'type'          => $appliedFilter['productType'],
                    'category'      => $categoriesFilter,
                    'price'         => $appliedFilter['price'],
                    'organicOnly'   => $appliedFilter['organicOnly'],
                    'search'        => $appliedFilter['search'],
                ],
                $appliedFilter['attributes'],
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
            ->getRepository(CommodityProduct::class)
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
         * @var User|null           $currentUser
         * @var CommodityProduct    $item
         */
        $currentUser = $this->getUser();

        return $this
            ->getDoctrine()
            ->getRepository(CommodityProduct::class)
            ->listFilter(
                $currentUser,
                null,
                [
                    'category'  => [$item->getCategory()->getId()],
                    'type'      => $item->getType(),
                    '!slug'     => $item->getId(),
                ]
            );
    }
    /**
     * @inheritdoc
     */
    protected function getSameItemsLink(Commodity $item): string
    {
        /** @var CommodityProduct $item */
        $filter = [
            'productType' => $item->getType(),
        ];

        if ($item->getCategory()->getParent()) {
            $filter['category']     = $item->getCategory()->getParent()->getId();
            $filter['subCategory']  = $item->getCategory()->getId();
        } else {
            $filter['category']     = $item->getCategory()->getId();
        }

        return $this->generateUrl('products_list', $filter);
    }
    /**
     * @inheritdoc
     */
    protected function provideCommoditySameItemsForSameSellers(Commodity $item): QueryBuilder
    {
        /**
         * @var User|null           $currentUser
         * @var CommodityProduct    $item
         */
        $currentUser = $this->getUser();

        return $this
            ->getDoctrine()
            ->getRepository(CommodityProduct::class)
            ->listFilter(
                $currentUser,
                null,
                [
                    'category'  => [$item->getCategory()->getId()],
                    '!slug'     => $item->getId(),
                ]
            );
    }

    private function getSameCategoryLink(Commodity $item): string
    {
        /** @var CommodityProduct $item */
        $filter = [];

        if ($item->getCategory()->getParent()) {
            $filter['category']     = $item->getCategory()->getParent()->getId();
        } else {
            $filter['category']     = $item->getCategory()->getId();
        }

        return $this->generateUrl('products_list', $filter);
    }
}
