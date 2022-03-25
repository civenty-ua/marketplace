<?php

namespace App\Controller;

use App\Helper\SeoHelper;
use App\Service\SeoService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\{Article, Category, Crop, Expert, Item, Partner, Tags, TypePage};
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class ItemController
 * @package App\Controller
 */
class ItemController extends AbstractController
{
    private const QUERY_PARAMETER_SEARCH = 'search';
    private const QUERY_PARAMETER_FILTER_CATEGORY = 'category';
    private const QUERY_PARAMETER_FILTER_CROP = 'crop';
    private const QUERY_PARAMETER_FILTER_PARTNER = 'partner';
    private const QUERY_PARAMETER_FILTER_EXPERT = 'expert';
    private const QUERY_PARAMETER_FILTER_TYPE = 'type';
    private const QUERY_PARAMETER_SORT_BY = 'sortBy';
    private const QUERY_PARAMETER_SORT_VALUES = [
        'viewsAmount',
        'createdAt',
    ];
    private const QUERY_PARAMETER_PAGE = 'page';
    private const LIST_PAGE_SIZE = 24;

    /**
     * @Route("/courses-and-webinars", name="courses_and_webinars")
     *
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @param SeoService $seoService
     * @return Response|string
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function list(PaginatorInterface $paginator, Request $request, SeoService $seoService): Response
    {
        $categoriesBlock = $this->getDoctrine()->getRepository(Category::class)->getHomePageCategories();
        $topTags = $this->getDoctrine()->getRepository(Tags::class)->getTopAll($request->getLocale());

        $countAll = $this->getDoctrine()->getRepository(Item::class)->countActiveItems();

        $pageTypeArticle = $this->getDoctrine()->getRepository(TypePage::class)->findOneBy(['code' => TypePage::TYPE_ARTICLE]);
        $pageTypeEcoArticle = $this->getDoctrine()->getRepository(TypePage::class)->findOneBy(['code' => TypePage::TYPE_ECO_ARTICLES]);
        $countArticle = $this->getDoctrine()->getRepository(Article::class)->getCountArticle([$pageTypeArticle->getId(), $pageTypeEcoArticle->getId()]);

        $pageTypeSuccessStories = $this->getDoctrine()->getRepository(TypePage::class)->findOneBy(['code' => TypePage::TYPE_SUCCESS_STORIES]);
        $countSuccessStories = $this->getDoctrine()->getRepository(Article::class)->getCountArticle([$pageTypeSuccessStories->getId()]);

        $coursesCount = $countAll['coursesCount'];
        $articlesCount = $countArticle;
        $webinarsCount = $countAll['webinarsCount'];
        $othersCount = $countAll['othersCount'];
        $occurrenceCount = $countAll['occurrenceCount'];
        $newsCount = $countAll['newsCount'];

        $all = $request->query->all();

        $search = null;
        if (!empty($all[self::QUERY_PARAMETER_SEARCH])) {
            $search = htmlspecialchars($all[self::QUERY_PARAMETER_SEARCH]);
        }

        $categoryFilter = null;
        if (!empty($all[self::QUERY_PARAMETER_FILTER_CATEGORY])) {
            $categoryFilter = explode(',', ($all[self::QUERY_PARAMETER_FILTER_CATEGORY]));
        }

        $cropFilter = null;
        if (!empty($all[self::QUERY_PARAMETER_FILTER_CROP])) {
            $cropFilter = explode(',', ($all[self::QUERY_PARAMETER_FILTER_CROP]));
        }

        $partnerFilter = null;
        if (!empty($all[self::QUERY_PARAMETER_FILTER_PARTNER])) {
            $partnerFilter = explode(',', ($all[self::QUERY_PARAMETER_FILTER_PARTNER]));
        }

        $expertFilter = null;
        if (!empty($all[self::QUERY_PARAMETER_FILTER_EXPERT])) {
            $expertFilter = explode(',', ($all[self::QUERY_PARAMETER_FILTER_EXPERT]));
        }

        $typeFilter = null;
        if (!empty($all[self::QUERY_PARAMETER_FILTER_TYPE])) {
            $typeFilter = explode(',', ($all[self::QUERY_PARAMETER_FILTER_TYPE]));
        }

        $sortBy = null;
        if (!empty($all[self::QUERY_PARAMETER_SORT_BY])) {
            $sortBy = $all[self::QUERY_PARAMETER_SORT_BY];
        }

        $page = null;
        if (!empty($all[self::QUERY_PARAMETER_PAGE])) {
            $page = $all[self::QUERY_PARAMETER_PAGE];
        }

        $typePage = $this->getDoctrine()->getRepository(TypePage::class)->findOneBy(['code' => 'news']);

        $categories = $this->getDoctrine()->getRepository(Category::class)->getAllSorted($typeFilter);
        $crops = $this->getDoctrine()->getRepository(Crop::class)->getAllSorted($typeFilter);
        $partners = $this->getDoctrine()->getRepository(Partner::class)->getAllSorted($typeFilter);
        $experts = $this->getDoctrine()->getRepository(Expert::class)->getAllSorted($typeFilter);

        $items = $this->getDoctrine()->getRepository(Item::class)->getFilteredItems(
            $categoryFilter,
            $cropFilter,
            $partnerFilter,
            $expertFilter,
            $typeFilter,
            $sortBy,
            $search,
            $request->getLocale(),
            $typePage
        );

        $items = $paginator->paginate(
            $items,
            $request->query->getInt(self::QUERY_PARAMETER_PAGE, 1),
            self::LIST_PAGE_SIZE
        );

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'content' => $this->render('blocks/item-panel/list-items.html.twig',
                    [
                        'items' => $items,
                    ])->getContent(),
                'filter' => $this->render('blocks/item-panel/filter-items.html.twig',
                    [
                        'categories' => $categories,
                        'crops' => $crops,
                        'partners' => $partners,
                        'experts' => $experts,
                        'appliedQueryParams' => [
                            self::QUERY_PARAMETER_FILTER_CATEGORY => $categoryFilter,
                            self::QUERY_PARAMETER_FILTER_CROP => $cropFilter,
                            self::QUERY_PARAMETER_FILTER_PARTNER => $partnerFilter,
                            self::QUERY_PARAMETER_FILTER_EXPERT => $expertFilter,
                        ],
                    ])->getContent(),
            ], Response::HTTP_OK);
        }

        $seo = null;
        if (is_array($typeFilter) && count($typeFilter) === 1) {
            $seo = $seoService
                ->setPage(SeoHelper::PAGE_COURSES_AND_WEBINARS, ['type' => $typeFilter[0]])
                ->getSeo();
        }

        $metaRobots = null;
        if ($page < 2 && (
            (is_array($typeFilter) && count($typeFilter) !== 1) ||
            !is_null($search) ||
            !is_null($categoryFilter) ||
            !is_null($cropFilter) ||
            !is_null($partnerFilter) ||
            !is_null($expertFilter) ||
            !is_null($sortBy))
        ) {
            $metaRobots = 'noindex, nofollow';
        }

        return $this->render('item/full.html.twig',
            [
                'seo' => $seo,
                'metaRobots' => $metaRobots,
                'items' => $items,
                'categoriesBlock' => $categoriesBlock,
                'topTags' => $topTags,
                'categories' => $categories,
                'crops' => $crops,
                'partners' => $partners,
                'experts' => $experts,
                'coursesCount' => $coursesCount,
                'articlesCount' => $articlesCount,
                'webinarsCount' => $webinarsCount,
                'otherCount' => $othersCount,
                'occurrenceCount' => $occurrenceCount,
                'newsCount' => $newsCount,
                'countSuccessStories' => $countSuccessStories,
                'appliedQueryParams' => [
                    self::QUERY_PARAMETER_SEARCH => $search,
                    self::QUERY_PARAMETER_FILTER_CATEGORY => $categoryFilter,
                    self::QUERY_PARAMETER_FILTER_CROP => $cropFilter,
                    self::QUERY_PARAMETER_FILTER_PARTNER => $partnerFilter,
                    self::QUERY_PARAMETER_FILTER_EXPERT => $expertFilter,
                    self::QUERY_PARAMETER_FILTER_TYPE => $typeFilter,
                    self::QUERY_PARAMETER_SORT_BY => $sortBy,
                    self::QUERY_PARAMETER_PAGE => $page,
                ],
                'filter' => [
                    'sortValues' => self::QUERY_PARAMETER_SORT_VALUES,
                ],
                'listAjaxUrl' => $this->generateUrl('courses_and_webinars',
                    [
                        self::QUERY_PARAMETER_SEARCH => 'SEARCH_VALUE',
                        self::QUERY_PARAMETER_FILTER_CATEGORY => 'CATEGORY_VALUES',
                        self::QUERY_PARAMETER_FILTER_CROP => 'CROP_VALUES',
                        self::QUERY_PARAMETER_FILTER_PARTNER => 'PARTNER_VALUES',
                        self::QUERY_PARAMETER_FILTER_EXPERT => 'EXPERT_VALUES',
                        self::QUERY_PARAMETER_FILTER_TYPE => 'TYPE_VALUES',
                        self::QUERY_PARAMETER_SORT_BY => 'SORT_VALUE',
                        self::QUERY_PARAMETER_PAGE => 'PAGE_VALUE',
                    ]),
            ]);
    }
}
