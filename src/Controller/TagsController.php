<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\{
    Request,
    Response,
};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use App\Helper\SeoHelper;
use App\Entity\{
    Item,
    Page,
    Tags,
};
/**
 * Class TagsController
 * @package App\Controller
 */
class TagsController extends AbstractController
{
    private const PAGE_SIZE = 24;
    /**
     * @Route("/tag/{slug}", name="tag_detail")
     *
     * @param string $slug
     * @param PaginatorInterface $paginator
     * @param Request $request
     *
     * @return Response
     */
    public function detail(string $slug, PaginatorInterface $paginator, Request $request): Response
    {
        /** @var Tags $tag */
        $tag            = $this
            ->getDoctrine()
            ->getRepository(Tags::class)
            ->findOneBy([
                'slug' => $slug,
            ]);

        if (!$tag) {
            throw new NotFoundHttpException("item $tag was not found");
        }

        $tagItems       = $this->getTagItems($tag);
        $currentPage    = $request->query->getInt('page', 1);
        $pagination     = $paginator->paginate($tagItems, $currentPage, self::PAGE_SIZE);
        $lastModified   = SeoHelper::formatLastModified($tag->getUpdatedAt());
        $response       = new Response();
        $response->headers->set('Last-Modified', $lastModified);

        return $this->render('tag/detail.html.twig', [
            'tag'       => $tag,
            'items'     => $pagination,
            'pagesList' => $this
                ->getDoctrine()
                ->getRepository(Page::class)
                ->findPageByTypeName('business_tools'),
        ], $response);
    }
    /**
     * Get tag items.
     *
     * @param   Tags $tag   Tag.
     *
     * @return  Item[]      Tag items set.
     */
    private function getTagItems(Tags $tag): iterable
    {
        $alias          = 'item';
        $aliasTags      = 'tag';
        $queryBuilder   = $this
            ->getDoctrine()
            ->getRepository(Item::class)
            ->createQueryBuilder($alias);

        $queryBuilder
            ->leftJoin("$alias.tags", $aliasTags)
            ->orderBy("$alias.createdAt", 'DESC')
            ->andWhere(
                $queryBuilder->expr()->eq("$alias.isActive", true)
            )
            ->andWhere(
                $queryBuilder->expr()->eq("$aliasTags.id", ':tag')
            )
            ->setParameter('tag', $tag->getId());

        return $queryBuilder->getQuery()->getResult();
    }
}
