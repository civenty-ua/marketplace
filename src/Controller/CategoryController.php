<?php

namespace App\Controller;

use App\Helper\SeoHelper;
use App\Service\SeoService;
use Symfony\{
    Bundle\FrameworkBundle\Controller\AbstractController,
    Contracts\Translation\TranslatorInterface,
    Component\Routing\Annotation\Route,
    Component\HttpFoundation\Request,
    Component\HttpFoundation\Response,
    Component\HttpKernel\Exception\NotFoundHttpException,
};
use App\Entity\{Article, Category, Course, News, Options, Page, Tags, TypePage, Webinar};
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class CategoryController
 * @package App\Controller
 */
class CategoryController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    private TranslatorInterface $translator;

    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    /**
     * @Route("/categories", name="categories")
     *
     * @return Response
     */
    public function index(): Response
    {
        $categories = $this->getDoctrine()->getRepository(Category::class)->findActiveCategories();

        return $this->render('category/index.html.twig',
            [
                'categories' => $categories,
            ]);
    }

    /**
     * @Route("/category/{slug}", name="category_detail")
     *
     * @param Request $request
     * @param string $slug
     *
     * @return Response
     */
    public function detail(Request $request, string $slug, SeoService $seoService): Response
    {
        $category = $this->entityManager->getRepository(Category::class)->findActiveBySlug($slug);

        if (!$category) {
            throw new NotFoundHttpException();
        }

        $tags = $category->getTags();

        $businessTools = $this->getDoctrine()
            ->getRepository(Page::class)
            ->findPageByTypeName(TypePage::TYPE_BUSINESS_TOOLS);

        $category->getCourseBanner() ? $promoCourse = $category->getCourseBanner()
            : $promoCourse =  $this->entityManager->getRepository(Course::class)
            ->getLastUpdatedCourseByCategory($category);

        $courses = $this->getDoctrine()
            ->getRepository(Course::class)
            ->findCourses($category);

        $webinars = $this->getDoctrine()
            ->getRepository(Webinar::class)
            ->findWebinars($category);

        $successStories = $this->getArticleWithTag(TypePage::TYPE_SUCCESS_STORIES, $category->getId());
        $news = $this->getDoctrine()->getRepository(News::class)->getNewsByCategory($category);
        $articles = $this->getArticleWithTag(TypePage::TYPE_ARTICLE, $category->getId());
        if (empty($successStories)) {
            $successStories = $this->getArticleWithoutCat(TypePage::TYPE_SUCCESS_STORIES);
        }

        $category->getLearningTitle() != null
            ? $learningTitle = $category->getLearningTitle()
            : $learningTitle = $this->translator->trans('item.category_learning_title');
        $category->getArticleTitle() != null
            ? $articleTitle = $category->getArticleTitle()
            : $articleTitle = $this->translator->trans('item.articles');
        $videos = [];
        $partners = [];
        $learning = array_merge($courses, $webinars);

        $seo = $seoService->setPage(SeoHelper::PAGE_CATEGORY)->getSeo(['title' => $category->getName()]);

        $lastModified = SeoHelper::formatLastModified($category->getUpdatedAt());

        $response = new Response();
        $response->headers->set('Last-Modified', $lastModified);

        return $this->render('category/detail.html.twig', [
            'seo' => $seoService->merge($seo, $category->getSeo()),
            'category' => $category,
            'tags' => $tags,
            'promoCourse' => $promoCourse,
            'learning' => $learning,
            'learningTitle' => $learningTitle,
            'articleTitle' => $articleTitle,
            'articles' => $articles,
            'businessTools' => $businessTools,
            'successStories' => $successStories,
            'news' => $news,
            'videos' => $videos,
            'partners' => $partners,
        ], $response);
    }

    public function getArticleWithoutCat(string $type)
    {
        return $this->getDoctrine()
            ->getRepository(Article::class)
            ->findArticleOnlyByTypeName($type);
    }

    public function getArticleWithTag(string $type, $tag)
    {
        return $this->getDoctrine()
            ->getRepository(Article::class)
            ->findArticleByTypeName($type, $tag);
    }
}
