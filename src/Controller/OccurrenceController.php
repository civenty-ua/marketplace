<?php

namespace App\Controller;

use App\Helper\SeoHelper;
use DateTime;

use App\Entity\{Category, Comment, ItemRegistration, Occurrence, Page, WebinarEstimation};
use App\Form\CommentType;
use App\Service\{ItemRatingService, UserHistoryManager};
use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Exception\Config\Filter\NotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use App\Event\Item\RequestEvent as ItemRequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class OccurrenceController
 * @package App\Controller
 */
class OccurrenceController extends AbstractController
{
    private const COMMENTS_PAGE_SIZE = 5;

    protected EventDispatcherInterface $eventDispatcher;

    protected HttpKernelInterface $kernel;

    /**
     * @var Security
     */
    private $security;

    private $itemRating;

    protected EntityManagerInterface $em;

    public function __construct(
        Security                 $security,
        EntityManagerInterface   $em,
        ItemRatingService        $itemRating,
        EventDispatcherInterface $eventDispatcher,
        HttpKernelInterface      $kernel
    )
    {
        $this->itemRating = $itemRating;
        $this->security = $security;
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
        $this->kernel = $kernel;
    }

    /**
     * @Route("/occurrence/{slug}", name="occurrence_detail")
     *
     * @param Request $request
     * @param UserInterface|null $user
     * @param UserHistoryManager $userHistoryManager
     * @param $slug
     *
     * @return Response
     * @throws NotFoundException
     */
    public function detail(
        Request             $request,
        ?UserInterface      $user,
        UserHistoryManager  $userHistoryManager,
        TranslatorInterface $translator,
                            $slug
    ): Response
    {
        /** @var Occurrence|null $occurrence */
        $occurrence = $this
            ->getDoctrine()
            ->getRepository(Occurrence::class)
            ->findOneBySlug($slug);

        if (is_null($occurrence) || $occurrence->getIsActive() != true) {
            throw new NotFoundException();
        }

        $userHasRatedOccurrence = false;
        $showSuccessRegistrationMsgBlock = null;

        if ($user) {
            $userHistoryManager->viewItem($user, $occurrence);
            !is_null($this->em->getRepository(WebinarEstimation::class)->findOneBy([
                'webinar' => $occurrence,
                'user' => $user,
            ]))
                ? $userHasRatedOccurrence = true
                : $userHasRatedOccurrence = false;
            $showSuccessRegistrationMsgBlock = $userHistoryManager->isShownSuccessRegisteredMessageBlock($occurrence, $user);
        }

        if ($request->isXmlHttpRequest()) {
            if (!$user) {
                return new JsonResponse([
                    'title' => $translator->trans('item.webinar_estimation_login'),
                    'login' => $this->generateUrl('login')
                ], 401);
            }
            if ($userHasRatedOccurrence === false) {
                $score = json_decode($request->getContent());
                $this->itemRating->updateWebinarRatingByAjax($occurrence, $score);
                $occurrenceEstimation = new WebinarEstimation();
                $occurrenceEstimation->setUser($user);
                $occurrenceEstimation->setWebinar($occurrence);
                $this->em->persist($occurrenceEstimation);
                $this->em->flush();

                return new JsonResponse([
                    'title' => $translator->trans('item.webinar_estimation'),
                    'message' => 'success',
                ], 200);
            }
        }

        $topOccurrences = $this->getDoctrine()->getRepository(Occurrence::class)->getTopOccurrences($occurrence->getId());
        $pagesList = $this->getDoctrine()->getRepository(Page::class)->findPageByTypeName('business_tools');
        $categories = $this->getDoctrine()->getRepository(Category::class)->findActiveCategories();
        $estimation = $this->itemRating->updateRating($occurrence);

        if($estimation['voted'] > $occurrence->getViewsAmount()){
            $occurrence->setViewsAmount($estimation['voted']  + rand(1,8));
        }

        $this->em->flush();
        $review = $this->itemRating->getReviews($occurrence);

        $registered = $userHistoryManager->registerButtonShow($occurrence, $user);

        /** @var Comment[] $comments */
        $commentsRepository = $this
            ->getDoctrine()
            ->getRepository(Comment::class);
        $commentsFilter = [
            'item' => $occurrence->getId(),
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

        $event->setItem($occurrence);

        $this->eventDispatcher->dispatch($event);

        $userCanRate = (
            $user &&
            $registered &&
            !$userHasRatedOccurrence &&
            $occurrence->getStartDate() < new DateTime('now')
        );

        $lastModified = SeoHelper::formatLastModified($occurrence->getUpdatedAt());

        $response = new Response();
        $response->headers->set('Last-Modified', $lastModified);

        return $this->render('occurrence/detail.html.twig', [
            'occurrence' => $occurrence,
            'topTags' => $occurrence->getTags(),
            'tagTitleFlag' => true,
            'categories' => $categories,
            'pagesList' => $pagesList,
            'voted' => $estimation['voted'],
            'rate' => $estimation['rate'],
            'review' => $review,
            'topItems' => $topOccurrences,
            'user' => $user,
            'registered' => $registered,
            'userHasRatedWebinar' => $userHasRatedOccurrence,
            'userCanRate' => $userCanRate,
            'showSuccessRegistrationMessageBlock' => $showSuccessRegistrationMsgBlock,
            'comments' => [
                'exist' => $comments,
                'form' => $this->createForm(CommentType::class)->createView(),
                'pageSize' => self::COMMENTS_PAGE_SIZE,
                'totalCount' => $commentsTotalCount,
            ],
        ], $response);
    }

    /**
     * @Route ("/occurrence/{slug}/register", name="occurrence_register")
     * @param Occurrence $occurrence
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function register(
        Occurrence             $occurrence,
        TranslatorInterface $translator
    ): Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->redirectToRoute('login');
        }

        $registeredUser = $this->getDoctrine()->getRepository(ItemRegistration::class)
            ->findOneBy(['userId' => $user->getId(), 'itemId' => $occurrence->getId()]);
        if ($registeredUser != null) {
            $this->addFlash('success', $translator->trans('item.occurrence_you_registered'));

            return $this->redirectToRoute('occurrence_detail', ['slug' => $occurrence->getSlug()]);
        }
        $this->persistItemRegistration($occurrence, $user);


        return $this->redirectToRoute('occurrence_detail', ['slug' => $occurrence->getSlug()]);
    }

    public function getRegisteredUser(Occurrence $occurrence, $user)
    {
        if (is_null($user)) {
            return null;
        }

        return $this->getDoctrine()->getRepository(ItemRegistration::class)
            ->findOneBy(['userId' => $user->getId(), 'itemId' => $occurrence->getId()]);
    }

    public function persistItemRegistration($occurrence, $user)
    {
        $registeredUser = new ItemRegistration();
        $registeredUser->setItemId($occurrence);
        $registeredUser->setUserId($user);
        $registeredUser->setItemType();
        $this->em->persist($registeredUser);
        $this->em->flush();
    }
}
