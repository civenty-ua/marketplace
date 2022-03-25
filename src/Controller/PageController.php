<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Options;
use App\Entity\Page;
use App\Helper\SeoHelper;
use App\Service\SeoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    /**
     * @Route("/{alias}",  priority=-10, name="custom_page")
     */
    public function index(string $alias, SeoService $seoService): Response
    {
        /** @var Page|null $page */
        $page = $this->getDoctrine()->getRepository(Page::class)->findOneBy(['alias' => $alias]);
        if (is_null($page)) {
            throw new NotFoundHttpException();
        }

        $seo = $seoService
            ->setPage(SeoHelper::PAGE_PAGE)
            ->getSeo(['title' => $page->getTitle()])
        ;

        $lastModified = SeoHelper::formatLastModified($page->getUpdatedAt());

        $response = new Response();
        $response->headers->set('Last-Modified', $lastModified);

        return $this->render('page/index.html.twig', [
            'seo' => $seoService->merge($seo, $page->getSeo()),
            'page' => $page,
        ], $response);
    }
}
