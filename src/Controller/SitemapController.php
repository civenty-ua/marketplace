<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Course;
use App\Entity\News;
use App\Entity\Page;
use App\Entity\Webinar;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SitemapController
 * @package App\Controller
 */
class SitemapController extends AbstractController
{
    /**
     * @Route("/sitemap.xml", name="sitemap_xml")
     *
     * @param Request $request
     * @return Response
     */
    public function sitemap(Request $request): Response
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'text/xml');

        $categories = $this->getDoctrine()->getRepository(Category::class)->findActiveCategories();
        $news = $this->getDoctrine()->getRepository(News::class)->findBy(['isActive' => true]);
        $articles = $this->getDoctrine()->getRepository(Article::class)->findBy(['isActive' => true]);
        $courses = $this->getDoctrine()->getRepository(Course::class)->findCourses();
        $webinars = $this->getDoctrine()->getRepository(Webinar::class)->findWebinars();

        return $this->render('sitemap/sitemap.xml.twig', [
            'categories' => $categories,
            'articles' => $articles,
            'courses' => $courses,
            'webinars' => $webinars,
            'newsList' => $news
        ], $response);
    }
}
