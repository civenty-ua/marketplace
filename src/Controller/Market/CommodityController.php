<?php
declare(strict_types = 1);

namespace App\Controller\Market;

use Doctrine\ORM\{
    QueryBuilder,
    EntityManagerInterface,
};
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Knp\Component\Pager\{
    PaginatorInterface,
    Pagination\PaginationInterface,
};
use App\Event\Commodity\CommodityRequestEvent;
use App\Entity\{
    Options,
    User,
    Market\Attribute,
    Market\Category,
    Market\CategoryAttributeParameters,
    Market\Commodity,
    Market\CommodityAttributeValue,
};
/**
 * Commodity abstract controller
 *
 * @package App\Controller
 */
abstract class CommodityController extends AbstractController
{
    protected const PAGE_SIZE           = 24;
    protected const SAME_ITEMS_LIMIT    = 9;
    protected const ITEM_ACTION_MAIN    = 'buy';
    protected const ITEM_ACTIONS        = [
        'view',
        'offerPrice',
        'toFavoriteToggle',
        'edit',
    ];
    protected const USER_ACTION_MAIN    = 'view';
    protected const USER_ACTIONS        = [
        'view',
        'toFavoriteToggle',
    ];

    private PaginatorInterface $paginator;
    private EventDispatcherInterface $eventDispatcher;
    /**
     * @var Options[] $options
     */
    protected array $options = [];

    public function __construct(
        PaginatorInterface $paginator,
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface $entityManager
    ) {
        $this->paginator = $paginator;
        $this->eventDispatcher = $eventDispatcher;
        /**
         * @var Options[] $options
         */
        $options = $entityManager->getRepository(Options::class)->findBy([
            'code' => [
                'market_goods_description_uk',
                'market_goods_description_en',
                'market_services_description_uk',
                'market_services_description_en',
                'market_proposals_description_uk',
                'market_proposals_description_en',
            ]
        ]);

        foreach ($options as $option) {
            $this->options[$option->getCode()] = $option;
        }
    }
    /**
     * Parse list filter from request.
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
    /**
     * Parse list filter from request.
     *
     * @param   Request $request            Request.
     *
     * @return  array                       Parsed filter.
     */
    protected function parseListFilter(Request $request): array
    {
        $requestData                = $request->getMethod() === 'POST'
            ? $request->request->all()
            : $request->query->all();

        $searchValue                = (string) ($requestData['search'] ?? '');
        $requestData['search']      = strlen($searchValue) > 0 ? $searchValue : null;

        $sortValue                  = $requestData['sortField'] ?? null;
        $sortAvailableValues        = $this->getSortAvailableValues();
        $requestData['sortField']   = in_array($sortValue, $sortAvailableValues)
            ? $sortValue
            : $sortAvailableValues[0];

        $pageValue                  = (int) ($requestData['page'] ?? 0);
        $requestData['page']        = $pageValue > 0 ? $pageValue : 1;

        return $requestData;
    }
    /**
     * Get categories set.
     *
     * @param   string      $type           Commodity type.
     * @param   int|null    $parentCategory Parent category ID.
     *
     * @return  Category[]                  Categories set.
     */
    protected function getCategoriesSet(string $type, ?int $parentCategory = null): array
    {
        /** @var Category[] $categories */
        $categories =  $this
            ->getDoctrine()
            ->getRepository(Category::class)
            ->findByCustom(
                [
                    'commodityType' => $type,
                    'parent'        => $parentCategory,
                ],
                ['title' => 'asc']
            );
        $result     = [];

        foreach ($categories as $category) {
            $result[$category->getId()] = $category;
        }

        return $result;
    }
    /**
     * Get category attributes parameters set.
     *
     * @param   int $categoryId                 Category ID.
     *
     * @return  CategoryAttributeParameters[]   Attributes parameters set.
     */
    protected function getCategoryAttributesParameters(int $categoryId): array
    {
        return $this
            ->getDoctrine()
            ->getRepository(CategoryAttributeParameters::class)
            ->findByCustom(
                ['category' => $categoryId],
                ['sort' => 'asc']
            );
    }
    /**
     * Get attributes max values.
     *
     * Use category attributes parameters and prepare attributes max values set.
     *
     * @param   CategoryAttributeParameters[] $categoryAttributes   Attributes parameters set.
     *
     * @return  array                                               Max values set, where
     *                                                              key is attribute parameter ID and
     *                                                              values is its max value
     */
    protected function getAttributesMaxValues(array $categoryAttributes): array
    {
        $maxValuesByCategoryId  = [];
        $result                 = [];

        foreach ($categoryAttributes as $categoryAttributeParameters) {
            if ($categoryAttributeParameters->getAttribute()->getType() !== Attribute::TYPE_INT) {
                continue;
            }

            $category = $categoryAttributeParameters->getCategory();

            if (!isset($maxValuesByCategoryId[$category->getId()])) {
                $maxValuesByCategoryId[$category->getId()] = $this
                    ->getDoctrine()
                    ->getRepository(CommodityAttributeValue::class)
                    ->getMaxIntegerValue(
                        $category,
                        $categoryAttributeParameters->getAttribute()
                    );
            }

            $result[$categoryAttributeParameters->getId()] = $maxValuesByCategoryId[$category->getId()];
        }

        return $result;
    }
    /**
     * Get commodity by ID.
     *
     * @param   int $id                     Commodity ID.
     *
     * @return  Commodity|null              Product, ia any
     */
    protected function getCommodityDetail(string $id): ?Commodity
    {
        $queryBuilder = $this->provideCommodityDetail($id);

        return $queryBuilder->getQuery()->getResult()[0] ?? null;
    }
    /**
     * Get same commodities for given commodity.
     *
     * @param   Commodity $item             Commodity.
     *
     * @return  Commodity[]                 Same commodities.
     */
    protected function getCommoditySameItems(Commodity $item): iterable
    {
        return $this->paginate(
            $this->provideCommoditySameItems($item),
            1,
            static::SAME_ITEMS_LIMIT
        )->getItems();
    }
    /**
     * Get same sellers for given commodity.
     *
     * @param   Commodity $item             Commodity.
     *
     * @return  User[]                      Same sellers.
     */
    protected function getCommoditySameSellers(Commodity $item): iterable
    {
        /** @var User|null $currentUser */
        $currentUser            = $this->getUser();
        $sameItemsQueryBuilder  = $this->provideCommoditySameItemsForSameSellers($item);
        $alias                  = $sameItemsQueryBuilder->getRootAliases()[0];
        $sameItems              = [];

        $sameItemsQueryBuilder->select("$alias.id");
        foreach ($sameItemsQueryBuilder->getQuery()->getResult() as $itemData) {
            $sameItems[] = $itemData['id'];
        }

        $sameSellersQueryBuilder = $this
            ->getDoctrine()
            ->getRepository(User::class)
            ->marketListFilter(
                $currentUser,
                null,
                [
                    '!id'           => $item->getUser()->getId(),
                    'commodities'   => array_unique($sameItems),
                ]
            );

        return $this->paginate(
            $sameSellersQueryBuilder,
            1,
            static::SAME_ITEMS_LIMIT
        )->getItems();
    }

    /**
     * Fire commodity request event.
     *
     * @param   Commodity $item             Commodity.
     *
     * @return  void
     */
    protected function fireCommodityRequestEvent(Commodity $item): void
    {
        $event = new CommodityRequestEvent($item);
        $this->eventDispatcher->dispatch($event);
    }
    /**
     * Get sort available values.
     *
     * @return string[]                     Sort available values.
     */
    abstract protected function getSortAvailableValues(): array;
    /**
     * Prepare fields data for output.
     *
     * @param   array $appliedFilter        Filter.
     *
     * @return  array                       Filter fields.
     */
    abstract protected function prepareFieldsData(array &$appliedFilter): array;
    /**
     * Build query builder for commodity detail query.
     *
     * @param   int $id                     Commodity ID.
     *
     * @return  QueryBuilder                Query builder.
     */
    abstract protected function provideCommodityDetail(string $id): QueryBuilder;
    /**
     * Build query builder for same commodities set.
     *
     * @param   Commodity $item             Commodity.
     *
     * @return  QueryBuilder                Query builder.
     */
    abstract protected function provideCommoditySameItems(Commodity $item): QueryBuilder;
    /**
     * Build query builder for same commodities set (for same sellers).
     *
     * @param   Commodity $item             Commodity.
     *
     * @return  QueryBuilder                Query builder.
     */
    abstract protected function provideCommoditySameItemsForSameSellers(Commodity $item): QueryBuilder;
    /**
     * Get same commodities link.
     *
     * @param   Commodity $item             Commodity.
     *
     * @return  string                      URL.
     */
    abstract protected function getSameItemsLink(Commodity $item): string;
}
