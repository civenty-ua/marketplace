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
use App\Entity\{Article, Category, Comment, Options, Page, TypePage, Tags};
use Knp\Component\Pager\{
    PaginatorInterface,
    Pagination\PaginationInterface,
};
use RuntimeException;

/**
 * Class EcoArticlesController
 * @package App\Controller
 */
class EcoArticlesController extends AbstractController
{
    private const PAGE_TYPE_CODE = 'eco_articles';
    private const COMMENTS_PAGE_SIZE = 5;

    private const QUERY_PARAMETER_SEARCH = 'search';
    private const QUERY_PARAMETER_SORT_BY = 'sortBy';
    private const QUERY_PARAMETER_SORT_BY_VALUES = [
        'createdAt',
        'title',
        'viewsAmount',
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
     * @Route("/eco-articles", name="eco_articles_list")
     *
     * @param Request $request Request.
     * @param PaginatorInterface $paginator Paginator.
     *
     * @return Response Response.
     */
    public function list(Request $request, PaginatorInterface $paginator, SeoService $seoService): Response
    {
        $basicFilter = $this->prepareListBasicFilter();
        $appliedFilter = $this->parseListFilter($request);

        $items = $this->queryListItems(
            $basicFilter,
            $appliedFilter,
            $request->getLocale(),
            $paginator
        );

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'content' => $this
                    ->render('eco_articles/list/items-bar.html.twig',
                        [
                            'items' => $items,
                        ])->getContent(),
                'url' => $this->generateUrl('eco_articles_list', $request->query->all()),
            ], Response::HTTP_OK);
        }

        $seo = $seoService->setPage(SeoHelper::PAGE_ECO_ARTICLES)->getSeo();

        return $this->render('eco_articles/list/full.html.twig',
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
                'filter' => [
                    'applied' => $appliedFilter,
                    'sortValues' => self::QUERY_PARAMETER_SORT_BY_VALUES,
                ],
                'items' => $items,
                'listAjaxUrl' => $this->generateUrl('eco_articles_list',
                    [
                        self::QUERY_PARAMETER_SEARCH => 'SEARCH_VALUE',
                        self::QUERY_PARAMETER_SORT_BY => 'SORT_BY_VALUE',
                        self::QUERY_PARAMETER_PAGE => 'PAGE_VALUE',
                    ]),
            ]);
    }

    /**
     * @Route("/eco-articles/{slug}", name="eco_articles_detail")
     *
     * @param string $slug Item slug.
     * @param Request $request Request.
     *
     * @return Response Response.
     */
    public function detail(string $slug, Request $request,?UserInterface $user): Response
    {
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

        $lastModified = SeoHelper::formatLastModified($item->getUpdatedAt());

        $response = new Response();
        $response->headers->set('Last-Modified', $lastModified);

        return $this->render('eco_articles/item/detail.html.twig', [
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
        ], );
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
            throw new RuntimeException("\"eco articles\" page type was not found");
        }

        return [
            'isActive' => true,
            'typePage' => $pageType->getId(),
        ];
    }

    /**
     * Parse list filter from request.
     *
     * @param Request $request Request.
     *
     * @return array Parsed filter.
     */
    private function parseListFilter(Request $request): array
    {
        $requestQuery = $request->query->all();
        $search = (string)($requestQuery[self::QUERY_PARAMETER_SEARCH] ?? '');
        $sortByValue = (string)($requestQuery[self::QUERY_PARAMETER_SORT_BY] ?? '');
        $sortBy = in_array($sortByValue, self::QUERY_PARAMETER_SORT_BY_VALUES)
            ? $sortByValue
            : self::QUERY_PARAMETER_SORT_BY_VALUES[0];

        $pageValue = (int)($requestQuery[self::QUERY_PARAMETER_PAGE] ?? 0);
        $page = $pageValue > 0 ? $pageValue : 1;

        return [
            self::QUERY_PARAMETER_SEARCH => $search,
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
        array              $basicFilter,
        array              $appliedFilter,
        string             $locale,
        PaginatorInterface $paginator
    ): PaginationInterface
    {
        $filter = $basicFilter;

        if (strlen($appliedFilter[self::QUERY_PARAMETER_SEARCH]) > 0) {
            $filter['content'] = $appliedFilter[self::QUERY_PARAMETER_SEARCH];
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
