<?php

namespace App\Controller;

use App\Event\Item\RequestEvent as ItemRequestEvent;
use App\Form\CommentType;
use App\Helper\SeoHelper;
use App\Service\SeoService;
use Symfony\{Bundle\FrameworkBundle\Controller\AbstractController,
    Component\EventDispatcher\EventDispatcherInterface,
    Component\HttpKernel\HttpKernelInterface,
    Component\Routing\Annotation\Route,
    Component\HttpFoundation\Request,
    Component\HttpFoundation\Response,
    Component\HttpFoundation\JsonResponse,
    Component\HttpKernel\Exception\NotFoundHttpException,
    Component\Security\Core\Security,
    Component\Security\Core\User\UserInterface};
use App\Entity\{Article, Category, Comment, Page, Region, TypePage, Tags};
use Knp\Component\Pager\{
    PaginatorInterface,
    Pagination\PaginationInterface,
};
use RuntimeException;

/**
 * Class SuccessStoriesController
 * @package App\Controller
 */
class SuccessStoriesController extends AbstractController
{
    private const PAGE_TYPE_CODE = 'success_stories';
    private const COMMENTS_PAGE_SIZE = 5;

    private const QUERY_PARAMETER_SEARCH = 'search';
    private const QUERY_PARAMETER_FILTER_REGION = 'region';
    private const QUERY_PARAMETER_REGIONS_IN_USE = [
        'kherson',
        'mykolaiv',
        'odesa',
        'zapogizdja',
    ];
    private const QUERY_PARAMETER_REGIONS_OTHERS = 'other';
    private const QUERY_PARAMETER_SORT_BY = 'sortBy';
    private const QUERY_PARAMETER_SORT_BY_VALUES = [
        'createdAt',
        'title',
    ];
    private const QUERY_PARAMETER_PAGE = 'page';
    private const LIST_PAGE_SIZE = 24;

    protected EventDispatcherInterface $eventDispatcher;
    protected HttpKernelInterface $kernel;
    protected Security $security;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        HttpKernelInterface $kernel,
        Security $security
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->kernel = $kernel;
        $this->security = $security;
    }
    /**
     * @Route("/success-stories", name="success_stories_list")
     *
     * @param Request $request Request.
     * @param PaginatorInterface $paginator Paginator.
     *
     * @return Response Response.
     */
    public function list(Request $request, PaginatorInterface $paginator, SeoService $seoService): Response
    {
        /** @var Region[] $regions */
        $regions = $this
            ->getDoctrine()
            ->getRepository(Region::class)
            ->findAll();
        $basicFilter = $this->prepareListBasicFilter();
        $appliedFilter = $this->parseListFilter($regions, $request);
        $regionsWithItemsCountData = $this->prepareRegionsWithItemsCountData(
            $basicFilter,
            $appliedFilter,
            $regions
        );
        $items = $this->queryListItems(
            $basicFilter,
            $appliedFilter,
            $request->getLocale(),
            $paginator
        );

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'content' => $this
                    ->render('success_stories/list/items-bar.html.twig',
                        [
                            'items' => $items,
                        ])->getContent(),
                'url' => $this->generateUrl('success_stories_list', $request->query->all()),
            ], Response::HTTP_OK);
        }

        $seo = $seoService->setPage(SeoHelper::PAGE_SUCCESS_STORIES)->getSeo();

        return $this->render('success_stories/list/full.html.twig',
            [
                'seo' => $seo,
                'categories' => $this
                    ->getDoctrine()
                    ->getRepository(Category::class)
                    ->findActiveCategories(),
                'topTags' => $this
                    ->getDoctrine()
                    ->getRepository(Tags::class)
                    ->getTopArticleTags($request->getLocale()),
                'pagesList' => $this
                    ->getDoctrine()
                    ->getRepository(Page::class)
                    ->findPageByTypeName('business_tools'),
                'regionsData' => $regionsWithItemsCountData,
                'filter' => [
                    'applied' => $appliedFilter,
                    'sortValues' => self::QUERY_PARAMETER_SORT_BY_VALUES,
                ],
                'items' => $items,
                'listAjaxUrl' => $this->generateUrl('success_stories_list',
                    [
                        self::QUERY_PARAMETER_SEARCH => 'SEARCH_VALUE',
                        self::QUERY_PARAMETER_FILTER_REGION => 'REGIONS_VALUE',
                        self::QUERY_PARAMETER_SORT_BY => 'SORT_BY_VALUE',
                        self::QUERY_PARAMETER_PAGE => 'PAGE_VALUE',
                    ]),
            ]);
    }

    /**
     * @Route("/success-stories/{slug}", name="success_stories_detail")
     *
     * @param string $slug Item slug.
     * @param Request $request Request.
     *
     * @return Response Response.
     */
    public function detail(
        string $slug,
        Request $request,
        SeoService $seoService,
        ?UserInterface $user
    ): Response {
        $basicFilter = $this->prepareListBasicFilter();
        $item = $this
            ->getDoctrine()
            ->getRepository(Article::class)
            ->findOneBy(array_merge($basicFilter,
                [
                    'slug' => $slug,
                ]));
        if (!$item) {
            throw new NotFoundHttpException("item $slug was not found");
        }

        if (!$item->getIsActive()) {
            throw new NotFoundHttpException("item $slug not active");
        }

        $item->increaseViewsAmount();
        $this->getDoctrine()->getManager()->flush();

        $pageList = $this
            ->getDoctrine()
            ->getRepository(Page::class)
            ->findPageByTypeName('business_tools');
        $categories = $this
            ->getDoctrine()
            ->getRepository(Category::class)
            ->findActiveCategories();
        if ($item->getSimilar()->isEmpty()) {
            $similarItemList = $this
                ->getDoctrine()
                ->getRepository(Article::class)
                ->getSimilar(self::PAGE_TYPE_CODE,$item->getItemCropsAndCategoriesIds());
        } else {
            $similarItemList = $item->getSimilar();
        }

        /** @var Comment[] $comments */
        $commentsRepository = $this
            ->getDoctrine()
            ->getRepository(Comment::class);
        $commentsFilter = [
            'item' => $item->getId(),
        ];
        $comments = $commentsRepository->findBy(
            $commentsFilter,
            ['createdAt' => 'DESC'],
            self::COMMENTS_PAGE_SIZE
        );
        $commentsTotalCount = $commentsRepository->getCount($commentsFilter);

        $event = new ItemRequestEvent(
            $this->kernel,
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );
        $event->setItem($item);
        $this->eventDispatcher->dispatch($event);

        $seo = $seoService
            ->setPage(SeoHelper::PAGE_SUCCESS_STORY)
            ->getSeo(['title' => $item->getTitle(), 'category' => $item->getCategory()])
        ;

        $lastModified = SeoHelper::formatLastModified($item->getUpdatedAt());

        $response = new Response();
        $response->headers->set('Last-Modified', $lastModified);

        return $this->render('success_stories/item/detail.html.twig', [
            'seo' => $seoService->merge($seo, $item->getSeo()),
            'item' => $item,
            'topTags' => $item->getTags(),
            'tagTitleFlag' => true,
            'pagesList' => $pageList,
            'categories' => $categories,
            'similarItemList' => $similarItemList,
            'user' => $user,
            'comments' => [
                'exist' => $comments,
                'form' => $this->createForm(CommentType::class)->createView(),
                'pageSize' => self::COMMENTS_PAGE_SIZE,
                'totalCount' => $commentsTotalCount,
            ],
        ], $response);
    }

    /**
     * Prepare items list basic/prime filter.
     *
     * @return array Items filter.
     */
    private function prepareListBasicFilter(): array
    {
        /** @var TypePage|null $pageType */
        $pageType = $this
            ->getDoctrine()
            ->getRepository(TypePage::class)
            ->findOneBy([
                'code' => self::PAGE_TYPE_CODE,
            ]);

        if (!$pageType) {
            throw new RuntimeException("\"history of success\" page type was not found");
        }

        return [
            'isActive' => true,
            'typePage' => $pageType->getId(),
        ];
    }

    /**
     * Prepare regions with items count data.
     *
     * @param array $basicFilter Items basic filter.
     * @param array $appliedFilter Items applied filter.
     * @param Region[] $regions Regions set.
     *
     * @return array Prepared data.
     */
    private function prepareRegionsWithItemsCountData(
        array $basicFilter,
        array $appliedFilter,
        array $regions
    ): array {
        $regionAppliedFilter = $appliedFilter[self::QUERY_PARAMETER_FILTER_REGION];
        $regionOthersFilterChecked = false;
        $itemsPerRegionsCount = $this
            ->getDoctrine()
            ->getRepository(Article::class)
            ->getItemsPerRegionsCount($basicFilter);
        $result = [];

        foreach ($regions as $region) {
            if (in_array($region->getCode(), self::QUERY_PARAMETER_REGIONS_IN_USE)) {
                $result[] = [
                    'region' => $region,
                    'itemsCount' => $itemsPerRegionsCount[$region->getId()] ?? 0,
                    'code' => $region->getCode(),
                    'checked' => in_array($region->getId(), $regionAppliedFilter),
                ];
                unset($itemsPerRegionsCount[$region->getId()]);
            } elseif (in_array($region->getId(), $regionAppliedFilter)) {
                $regionOthersFilterChecked = true;
            }
        }

        $result[] = [
            'region' => null,
            'itemsCount' => array_sum($itemsPerRegionsCount),
            'code' => self::QUERY_PARAMETER_REGIONS_OTHERS,
            'checked' => $regionOthersFilterChecked,
        ];

        return $result;
    }

    /**
     * Parse list filter from request.
     *
     * @param Region[] $regions Regions set.
     * @param Request $request Request.
     *
     * @return array Parsed filter.
     */
    private function parseListFilter(array $regions, Request $request): array
    {
        $requestQuery = $request->query->all();
        $search = (string)($requestQuery[self::QUERY_PARAMETER_SEARCH] ?? '');

        $regionValue = (string)($requestQuery[self::QUERY_PARAMETER_FILTER_REGION] ?? '');
        $regionValueExploded = explode(',', $regionValue);
        $regionsCodesSet = [];
        $regionsIdSet = [];
        $regionFilterOtherExist = false;

        foreach ($regionValueExploded as $value) {
            if (in_array($value, self::QUERY_PARAMETER_REGIONS_IN_USE)) {
                $regionsCodesSet[] = $value;
            } elseif ($value === self::QUERY_PARAMETER_REGIONS_OTHERS) {
                $regionFilterOtherExist = true;
            }
        }

        foreach ($regions as $region) {
            if (
                in_array($region->getCode(), $regionsCodesSet)
                || (
                    !in_array($region->getCode(), self::QUERY_PARAMETER_REGIONS_IN_USE)
                    && $regionFilterOtherExist
                )
            ) {
                $regionsIdSet[] = $region->getId();
            }
        }

        if ($regionFilterOtherExist) {
            $regionsIdSet[] = null;
        }

        $sortByValue = (string)($requestQuery[self::QUERY_PARAMETER_SORT_BY] ?? '');
        $sortBy = in_array($sortByValue, self::QUERY_PARAMETER_SORT_BY_VALUES)
            ? $sortByValue
            : self::QUERY_PARAMETER_SORT_BY_VALUES[0];

        $pageValue = (int)($requestQuery[self::QUERY_PARAMETER_PAGE] ?? 0);
        $page = $pageValue > 0 ? $pageValue : 1;

        return [
            self::QUERY_PARAMETER_SEARCH => $search,
            self::QUERY_PARAMETER_FILTER_REGION => $regionsIdSet,
            self::QUERY_PARAMETER_SORT_BY => $sortBy,
            self::QUERY_PARAMETER_PAGE => $page,
        ];
    }

    /**
     * Query items set for list, using filters parameters.
     *
     * @param array $basicFilter Items basic filter.
     * @param array $appliedFilter Items applied filter.
     * @param string $locale Locale.
     * @param PaginatorInterface $paginator Paginator.
     *
     * @return PaginationInterface Items.
     */
    private function queryListItems(
        array $basicFilter,
        array $appliedFilter,
        string $locale,
        PaginatorInterface $paginator
    ): PaginationInterface {
        $filter = $basicFilter;

        if (strlen($appliedFilter[self::QUERY_PARAMETER_SEARCH]) > 0) {
            $filter['content'] = $appliedFilter[self::QUERY_PARAMETER_SEARCH];
        }
        if (count($appliedFilter[self::QUERY_PARAMETER_FILTER_REGION]) > 0) {
            $filter['region'] = $appliedFilter[self::QUERY_PARAMETER_FILTER_REGION];
        }

        $items = $this
            ->getDoctrine()
            ->getRepository(Article::class)
            ->findByAlt(
                $filter,
                $locale,
                [
                    $appliedFilter[self::QUERY_PARAMETER_SORT_BY] => 'DESC',
                ]
            );

        return $paginator->paginate(
            $items,
            $appliedFilter[self::QUERY_PARAMETER_PAGE],
            self::LIST_PAGE_SIZE
        );
    }
}
