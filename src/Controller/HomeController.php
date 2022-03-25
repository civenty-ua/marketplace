<?php

namespace App\Controller;

use App\Helper\SeoHelper;
use App\Service\SeoService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\{Article, Category, Course, Item, Market\Commodity, News, Options, Review, Tags, User, Webinar};
use Doctrine\ORM\{EntityManagerInterface, NonUniqueResultException, NoResultException};

/**
 * Class HomeController
 * @package App\Controller
 */
class HomeController extends AbstractController
{
    /**
     * @var Options[] $options
     */
    private array $options = [];

    public function __construct(EntityManagerInterface $entityManager)
    {
        /**
         * @var Options[] $options
         */
        $options = $entityManager->getRepository(Options::class)->findBy([
           'code' => [
               'index_page_counter_webinar_value',
               'index_page_counter_course_value',
               'index_page_counter_commodity_value',
               'index_page_counter_buyer_value',
               'index_page_counter_webinar_description_uk',
               'index_page_counter_webinar_description_en',
               'index_page_counter_course_description_uk',
               'index_page_counter_course_description_en',
               'index_page_counter_commodity_description_uk',
               'index_page_counter_commodity_description_en',
               'index_page_counter_buyer_description_uk',
               'index_page_counter_buyer_description_en',
           ]
        ]);

        foreach ($options as $option) {
            $this->options[$option->getCode()] = $option;
        }
    }

    /**
     * @Route("/", name="home")
     *
     * @param Request $request
     * @param SeoService $seoService
     * @return Response
     */
    public function index(Request $request, SeoService $seoService): Response
    {
        $categories = $this->getDoctrine()->getRepository(Category::class)->getHomePageCategories();

        $webinarsCountManual = (int) $this->options['index_page_counter_webinar_value']->getValue();
        $coursesCountManual = (int) $this->options['index_page_counter_course_value']->getValue();
        $productsCountManual = (int) $this->options['index_page_counter_commodity_value']->getValue();
        $usersCountManual = (int) $this->options['index_page_counter_buyer_value']->getValue();

        $webinarCountTitleUk = $this->options['index_page_counter_webinar_description_uk']->getValue();
        $webinarCountTitleEn = $this->options['index_page_counter_webinar_description_en']->getValue();
        $courseCountTitleUk = $this->options['index_page_counter_course_description_uk']->getValue();
        $courseCountTitleEn = $this->options['index_page_counter_course_description_en']->getValue();
        $commodityCountTitleUk = $this->options['index_page_counter_commodity_description_uk']->getValue();
        $commodityCountTitleEn = $this->options['index_page_counter_commodity_description_en']->getValue();
        $userCountTitleUk = $this->options['index_page_counter_buyer_description_uk']->getValue();
        $userCountTitleEn = $this->options['index_page_counter_buyer_description_en']->getValue();

        $webinars = $webinarsCountManual > 0
            ? $webinarsCountManual
            : $this->getDoctrine()->getRepository(Webinar::class)->activeWebinarsCount();

        $courses = $coursesCountManual > 0
            ? $coursesCountManual
            : $this->getDoctrine()->getRepository(Course::class)->activeCoursesCount();

        $products = $productsCountManual > 0
            ? $productsCountManual
            : $this->getDoctrine()->getRepository(Commodity::class)->getTotalCount(null);
        $usersCount = $usersCountManual > 0
            ? $usersCountManual
            : $this->getDoctrine()->getRepository(User::class)->getTotalCount(null);

        try {
            $successStoryCount = $this->getDoctrine()->getRepository(Article::class)->getCountSuccessesStory();
        } catch (NoResultException | NonUniqueResultException $e) {
            $successStoryCount = 0;
        }

        $topItems = $this->getDoctrine()->getRepository(Item::class)->getTopItems();
        $reviews = $this->getDoctrine()->getRepository(Review::class)->findBy(['isTop' => true]);
        $topTags = $this->getDoctrine()->getRepository(Tags::class)->getTopAll($request->getLocale());

        $seo = $seoService->setPage(SeoHelper::PAGE_HOME)->getSeo();

        $lastModified = SeoHelper::formatLastModified(
            $this->getDoctrine()->getRepository(News::class)->getLastModified()
        );

        $response = new Response();
        $response->headers->set('Last-Modified', $lastModified);

        return $this->render('home/landing.html.twig', [
            'seo' => $seo,
            'users' => $usersCount,
            'products' => $products,
            'categories' => $categories,
            'webinars' => $webinars,
            'courses' => $courses,
            'topItems' => $topItems,
            'reviews' => $reviews,
            'badgeFlag' => true,
            'topTags' => $topTags,
            'successStoryCount' => $successStoryCount,
            'webinar_count_title_uk' => $webinarCountTitleUk,
            'webinar_count_title_en' => $webinarCountTitleEn,
            'course_count_title_uk' => $courseCountTitleUk,
            'course_count_title_en' => $courseCountTitleEn,
            'commodity_count_title_uk' => $commodityCountTitleUk,
            'commodity_count_title_en' => $commodityCountTitleEn,
            'user_count_title_uk' => $userCountTitleUk,
            'user_count_title_en' => $userCountTitleEn,
        ], $response);
    }

    /**
     * @Route("/feedback-form", name="feedback_form")
     */
    public function feedbackForm(): Response
    {
        return $this->render('demo/feedback-form.twig');
    }
}
