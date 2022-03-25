<?php
declare(strict_types = 1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\{
    Request,
    Response,
};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use App\Entity\{
    Category,
    Item,
    Page,
    Tags,
};
/**
 * Class SearchController
 *
 * @package App\Controller
 */
class SearchController extends AbstractController
{
    private const PAGE_SIZE                 = 24;
    private const MARKETPLACE_URL_PREFIX    = 'marketplace';
    /**
     * @Route("/search-all", name="search-all", methods={"GET"})
     *
     * @param PaginatorInterface $paginator
     * @param Request $request
     *
     * @return Response
     */
    public function searchAll(Request $request, PaginatorInterface $paginator): Response
    {
        $searchString = $request->query->get('q');

        if ($this->isMarketplaceRequest($request)) {
            return $this->redirectToRoute('commodities_search', [
                'search' => $searchString,
            ]);
        }

        $result = $this
            ->getDoctrine()
            ->getRepository(Item::class)
            ->search($searchString, $request->getLocale());

        return $this->render('search/search-all.html.twig',
            [
                'items' => $paginator->paginate(
                    $result,
                    $request->query->getInt('page', 1),
                    self::PAGE_SIZE
                ),
                'pagesList' => $this
                    ->getDoctrine()
                    ->getRepository(Page::class)
                    ->findPageByTypeName('business_tools'),
                'categories' => $this
                    ->getDoctrine()
                    ->getRepository(Category::class)
                    ->findActiveCategories(),
                'topTags' => $this
                    ->getDoctrine()
                    ->getRepository(Tags::class)
                    ->getTopArticleTags($request->getLocale()),
                'searchString' => $searchString,
            ]);
    }
    /**
     * @param Request $request
     *
     * @return bool
     */
    private function isMarketplaceRequest(Request $request): bool
    {
        $referer = $request->server->get('HTTP_REFERER');

        return strripos($referer, self::MARKETPLACE_URL_PREFIX) !== false;
    }
}
