<?php
declare(strict_types = 1);

namespace App\Controller\Market;

use Doctrine\{
    ORM\QueryBuilder,
    Persistence\ObjectRepository,
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    JsonResponse,
};
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\Pagination\PaginationInterface;
use App\Repository\Market\CommodityRepository;
use App\Entity\{
    User,
    Market\Category,
    Market\CategoryAttributeParameters,
    Market\Commodity,
    Market\CommodityProduct,
    Market\CommodityService,
    Market\CommodityKit,
};
/**
 * Products controller.
 *
 * @package App\Controller
 */
class CommoditySearchController extends CommodityController
{
    /**
     * @Route("/marketplace/search", name="commodities_search")
     */
    public function search(Request $request): Response
    {
        $appliedFilter  = $this->parseListFilter($request);
        $fieldsData     = $this->prepareFieldsData($appliedFilter);

        return $this->render('market/commodity/search/list.html.twig', [
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
     *     "/marketplace/search/ajax/list-rebuild",
     *     name     = "commodities_search_ajax_rebuild",
     *     methods  = "POST"
     * )
     */
    public function searchAjaxRebuild(Request $request): Response
    {
        $appliedFilter  = $this->parseListFilter($request);
        $fieldsData     = $this->prepareFieldsData($appliedFilter);

        return new JsonResponse([
            'url'               => $this->generateUrl('commodities_search', $appliedFilter),
            'filter'            => $this
                ->render('market/commodity/search/filter.html.twig', [
                    'fields'            => $fieldsData,
                    'filter'            => $appliedFilter,
                ])
                ->getContent(),
            'appliedFiltersBar' => $this
                ->render('market/commodity/search/listAppliedFilterBar.html.twig', [
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
     * @inheritdoc
     */
    protected function getSortAvailableValues(): array
    {
        return CommodityRepository::getListFilterAvailableSortValues();
    }
    /**
     * @inheritdoc
     */
    protected function parseListFilter(Request $request): array
    {
        $requestData    = parent::parseListFilter($request);
        $categoryId     = (int)     ($requestData['category']       ?? 0);
        $subCategoryId  = (int)     ($requestData['subCategory']    ?? 0);
        $commodityType  = (string)  ($requestData['commodityType']  ?? '');
        $commodityType  = in_array($commodityType, [
            Commodity::TYPE_PRODUCT,
            Commodity::TYPE_SERVICE,
            Commodity::TYPE_KIT,
        ]) ? $commodityType : null;
        $productsTypes  = (array)   ($requestData['productType']    ?? []);
        $productsTypes  = array_filter($productsTypes, function($value) {
            return in_array($value, CommodityProduct::getAvailableTypes());
        });
        $organicOnly    = isset($requestData['organicOnly'])
            ? (bool) ($requestData['organicOnly'] ?? false)
            : null;
        $priceFrom      = (int)     ($requestData['price'][0]       ?? 0);
        $priceTo        = (int)     ($requestData['price'][1]       ?? 0);
        $attributes     = (array)   ($requestData['attributes']     ?? []);

        return array_merge($requestData, [
            'commodityType' => $commodityType,
            'category'      => $categoryId > 0 && $commodityType !== Commodity::TYPE_KIT
                ? $categoryId
            : null,
            'subCategory'   => $categoryId > 0 && $subCategoryId > 0 && $commodityType !== Commodity::TYPE_KIT
                ? $subCategoryId
                : null,
            'price'         => [
                $priceFrom  > 0 ? $priceFrom    : null,
                $priceTo    > 0 ? $priceTo      : null,
            ],
            'productType'   => $commodityType === Commodity::TYPE_PRODUCT ? $productsTypes  : [],
            'organicOnly'   => $commodityType === Commodity::TYPE_PRODUCT ? $organicOnly    : null,
            'attributes'    => $attributes,
        ]);
    }
    /**
     * @inheritdoc
     */
    protected function prepareFieldsData(array &$appliedFilter): array
    {
        $categories         = [];
        $subCategories      = [];
        $categoryAttributes = [];

        if (in_array($appliedFilter['commodityType'], [
            Commodity::TYPE_PRODUCT,
            Commodity::TYPE_SERVICE,
        ])) {
            $categories                     = $this->getCategoriesSet($appliedFilter['commodityType']);
            $appliedFilter['category']      = isset($categories[$appliedFilter['category']])
                ? $appliedFilter['category']
                : null;
            $subCategories                  = $appliedFilter['category']
                ? $this->getCategoriesSet($appliedFilter['commodityType'], $appliedFilter['category'])
                : [];
            $appliedFilter['subCategory']   = isset($subCategories[$appliedFilter['subCategory']])
                ? $appliedFilter['subCategory']
                : null;

            if ($appliedFilter['subCategory']) {
                $categoryAttributes = $this->getCategoryAttributesParameters($appliedFilter['subCategory']);
            } elseif ($appliedFilter['category'] && count($subCategories) === 0) {
                $categoryAttributes = $this->getCategoryAttributesParameters($appliedFilter['category']);
            }
        }

        return [
            'availableSortValues'   => $this->getSortAvailableValues(),
            'categories'            => $categories,
            'subCategories'         => $subCategories,
            'attributes'            => $categoryAttributes,
            'maxValues'             => [
                'price'                 => $this
                    ->getCommodityRepository($appliedFilter['commodityType'])
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
        $repository         = $this->getCommodityRepository($appliedFilter['commodityType']);
        $repositoryFilter   = [
            'price'     => $appliedFilter['price'],
            'search'    => $appliedFilter['search'],
        ];
        $getCategoryFilter  = function() use($appliedFilter, $subCategories): array {
            $result = [];

            if ($appliedFilter['subCategory']) {
                $result = [$appliedFilter['subCategory']];
            } elseif ($appliedFilter['category']) {
                $result = [$appliedFilter['category']];
                foreach ($subCategories as $subCategory) {
                    $result[] = $subCategory->getId();
                }
            }

            return $result;
        };

        switch ($appliedFilter['commodityType']) {
            case Commodity::TYPE_PRODUCT:
                $repositoryFilter['type']           = $appliedFilter['productType'];
                $repositoryFilter['organicOnly']    = $appliedFilter['organicOnly'];
                $repositoryFilter['category']       = $getCategoryFilter();
                break;
            case Commodity::TYPE_SERVICE:
                $repositoryFilter['category']       = $getCategoryFilter();
                break;
            default:
        }

        $itemsQuery = $repository->listFilter(
            $currentUser,
            $appliedFilter['sortField'],
            $repositoryFilter,
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
        return $this
            ->getDoctrine()
            ->getRepository(Commodity::class)
            ->listFilter(null, null);
    }
    /**
     * @inheritdoc
     */
    protected function provideCommoditySameItems(Commodity $item): QueryBuilder
    {
        return $this->provideCommodityDetail(0);
    }
    /**
     * @inheritdoc
     */
    protected function getSameItemsLink(Commodity $item): string
    {
        return '';
    }
    /**
     * @inheritdoc
     */
    protected function provideCommoditySameItemsForSameSellers(Commodity $item): QueryBuilder
    {
        return $this->provideCommodityDetail(0);
    }
    /**
     * Get repository for given commodity type.
     *
     * @param   string|null $commodityType  Commodity type.
     *
     * @return  ObjectRepository            Repository.
     */
    private function getCommodityRepository(?string $commodityType): ObjectRepository
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
                return $entityManager->getRepository(Commodity::class);
        }
    }
}
