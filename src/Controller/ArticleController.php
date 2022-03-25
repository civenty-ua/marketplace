<?php

namespace App\Controller;

use Symfony\{
    Bundle\FrameworkBundle\Controller\AbstractController,
    Component\EventDispatcher\EventDispatcherInterface,
    Component\Routing\Annotation\Route,
    Component\HttpFoundation\Request,
    Component\HttpFoundation\Response,
    Component\HttpKernel\HttpKernelInterface,
    Component\HttpKernel\Exception\NotFoundHttpException,
    Component\Security\Core\User\UserInterface,
    Component\Security\Core\Security,
};
use App\{Entity\Article,
    Entity\Comment,
    Entity\Category,
    Entity\Page,
    Entity\Tags,
    Form\CommentType,
    Event\Item\RequestEvent as ItemRequestEvent,
    Helper\SeoHelper,
    Service\SeoService};

/**
 * Class ArticleController
 * @package App\Controller
 */
class ArticleController extends AbstractController
{
    private const COMMENTS_PAGE_SIZE = 5;

    private const PAGE_TYPE_CODE = 'article';

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
     * @Route("/article/{slug}", name="article_detail")
     *
     * @param Request $request
     * @param UserInterface|null $user
     * @param $slug
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detail(
        Request $request,
        SeoService $seoService,
        ?UserInterface $user,
        $slug
    ): Response {
        $categories = $this->getDoctrine()->getRepository(Category::class)->findActiveCategories();
        $pageList = $this->getDoctrine()->getRepository(Page::class)->findPageByTypeName('business_tools');
        //todo add reviews when it will be ready
        /** @var Article|null $article */
        $article = $this->getDoctrine()->getRepository(Article::class)->findOneBy(['slug' => $slug]);
        if (is_null($article) or $article->getIsActive() != true) {
            throw new NotFoundHttpException();
        }
        if ($article->getSimilar()->isEmpty()) {
            $similarItemList = $this
                ->getDoctrine()
                ->getRepository(Article::class)
                ->getSimilar(self::PAGE_TYPE_CODE,$article->getItemCropsAndCategoriesIds());
        } else {
            $similarItemList = $article->getSimilar();
        }
        /** @var Comment[] $comments */
        $commentsRepository = $this
            ->getDoctrine()
            ->getRepository(Comment::class);
        $commentsFilter = [
            'item' => $article->getId(),
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
        $event->setItem($article);
        $this->eventDispatcher->dispatch($event);

        $seo = $seoService
            ->setPage(SeoHelper::PAGE_ARTICLE)
            ->getSeo(['title' => $article->getTitle(), 'category' => $article->getCategory()])
        ;

        $lastModified = SeoHelper::formatLastModified($article->getUpdatedAt());

        $response = new Response();
        $response->headers->set('Last-Modified', $lastModified);

        return $this->render('article/detail.html.twig', [
            'seo' => $seoService->merge($seo, $article->getSeo()),
            'categories' => $categories,
            'topTags' => $article->getTags(),
            'tagTitleFlag' => true,
            'pagesList' => $pageList,
            'article' => $article,
            'similar' => $similarItemList,
            'user' => $user,
            'comments' => [
                'exist' => $comments,
                'form' => $this->createForm(CommentType::class)->createView(),
                'pageSize' => self::COMMENTS_PAGE_SIZE,
                'totalCount' => $commentsTotalCount,
            ],
        ], $response);
    }
}
