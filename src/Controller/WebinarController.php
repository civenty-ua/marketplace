<?php

namespace App\Controller;

use App\Helper\SeoHelper;
use DateTime;
use App\Helper\ZoomRequestHelper;
use App\Repository\WebinarEstimationRepository;
use App\Entity\{Category, Comment, ItemRegistration, Page, Tags, Webinar, WebinarEstimation};
use App\Form\CommentType;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use App\Service\{ItemRatingService, SeoService, UserHistoryManager, ZoomClient};
use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Exception\Config\Filter\NotFoundException;
use PhpOffice\PhpSpreadsheet\Calculation\Web;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use App\Event\Item\RequestEvent as ItemRequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class WebinarController
 * @package App\Controller
 */
class WebinarController extends AbstractController
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
     * @Route("/webinar/{slug}", name="webinar_detail")
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
        SeoService          $seoService,
                            $slug
    ): Response
    {
        /** @var Webinar|null $webinar */
        $webinar = $this
            ->getDoctrine()
            ->getRepository(Webinar::class)
            ->findOneBySlug($slug);

        if (is_null($webinar) or $webinar->getIsActive() != true) {
            throw new NotFoundException();
        }

        $userHasRatedWebinar = false;
        $showSuccessRegistrationMsgBlock = null;

        if ($user) {
            $userHistoryManager->viewWebinar($user, $webinar);
            !is_null($this->em->getRepository(WebinarEstimation::class)->findOneBy([
                'webinar' => $webinar,
                'user' => $user,
            ]))
                ? $userHasRatedWebinar = true
                : $userHasRatedWebinar = false;
            $showSuccessRegistrationMsgBlock = $userHistoryManager->isShownSuccessRegisteredMessageBlock($webinar, $user);
        }

        if ($request->isXmlHttpRequest()) {
            if (!$user) {
                return new JsonResponse([
                    'title' => $translator->trans('item.webinar_estimation_login'),
                    'login' => $this->generateUrl('login')
                ], 401);
            }
            if ($userHasRatedWebinar === false) {
                $score = json_decode($request->getContent());
                $this->itemRating->updateWebinarRatingByAjax($webinar, $score);
                $webinarEstimation = new WebinarEstimation();
                $webinarEstimation->setUser($user);
                $webinarEstimation->setWebinar($webinar);
                $this->em->persist($webinarEstimation);
                $this->em->flush();

                return new JsonResponse([
                    'title' => $translator->trans('item.webinar_estimation'),
                    'message' => 'success',
                ], 200);
            }
        }

        $topWebinars = $this->getDoctrine()->getRepository(Webinar::class)->getTopWebinars($webinar->getId());
        $pagesList = $this->getDoctrine()->getRepository(Page::class)->findPageByTypeName('business_tools');
        $categories = $this->getDoctrine()->getRepository(Category::class)->findActiveCategories();
        $estimation = $this->itemRating->updateRating($webinar);

        if($estimation['voted'] > $webinar->getViewsAmount()){
            $webinar->setViewsAmount($estimation['voted'] + rand(1,8));
        }

        $this->em->flush();
        $review = $this->itemRating->getReviews($webinar);

        $registered = $userHistoryManager->registerButtonShow($webinar, $user);

        /** @var Comment[] $comments */
        $commentsRepository = $this
            ->getDoctrine()
            ->getRepository(Comment::class);
        $commentsFilter = [
            'item' => $webinar->getId(),
        ];
        $comments = $commentsRepository->findBy(
            $commentsFilter,
            ['createdAt' => 'DESC'],
            self::COMMENTS_PAGE_SIZE
        );

        $commentsTotalCount = $commentsRepository->getCount($commentsFilter);

        try {
            $countRegisteredUser = $this->getDoctrine()->getRepository(ItemRegistration::class)
                ->getCountUserInWebinar($webinar);
        } catch (NoResultException | NonUniqueResultException $e) {
            $countRegisteredUser = 0;
        }

        $event = new ItemRequestEvent(
            $this->kernel,
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $event->setItem($webinar);

        $this->eventDispatcher->dispatch($event);

        $userCanRate = (
            $user &&
            $registered &&
            !$userHasRatedWebinar &&
            $webinar->getStartDate() < new DateTime('now')
        );

        $seo = $seoService
            ->setPage(SeoHelper::PAGE_WEBINAR)
            ->getSeo(['title' => $webinar->getTitle(), 'category' => $webinar->getCategory()])
        ;

        $lastModified = SeoHelper::formatLastModified($webinar->getUpdatedAt());

        $response = new Response();
        $response->headers->set('Last-Modified', $lastModified);

        return $this->render('webinar/detail.html.twig', [
            'seo' => $seoService->merge($seo, $webinar->getSeo()),
            'webinar' => $webinar,
            'topTags' => $webinar->getTags(),
            'tagTitleFlag' => true,
            'categories' => $categories,
            'pagesList' => $pagesList,
            'voted' => $estimation['voted'],
            'rate' => $estimation['rate'],
            'review' => $review,
            'topItems' => $topWebinars,
            'countRegisteredUser' => $countRegisteredUser,
            'user' => $user,
            'registered' => $registered,
            'userHasRatedWebinar' => $userHasRatedWebinar,
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
     * @Route ("/webinar/{slug}/register", name="webinar_register")
     *
     * @param Webinar $webinar
     * @param MailerInterface $mailer
     * @param ContainerInterface $container
     * @param TranslatorInterface $translator
     * @param LoggerInterface $logger
     *
     * @return Response
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function register(
        Webinar             $webinar,
        MailerInterface     $mailer,
        ContainerInterface  $container,
        TranslatorInterface $translator,
        LoggerInterface     $logger,
        ZoomRequestHelper   $zoomRequestHelper
    ): Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->redirectToRoute('login');
        }
        if (is_null($webinar)) {
            throw new NotFoundHttpException();
        }
        $registeredUser = $this->getDoctrine()->getRepository(ItemRegistration::class)
            ->findOneBy(['userId' => $user->getId(), 'itemId' => $webinar->getId()]);
        if ($registeredUser != null) {
            $this->addFlash('success', $translator->trans('item.webinar_you_registered'));

            return $this->redirectToRoute('webinar_detail', ['slug' => $webinar->getSlug()]);
        }

        if (!$webinar->getMeetingId()) {
            $this->addFlash('success', $translator->trans('item.webinar_registration_success'));

            $this->persistItemRegistration($webinar, $user);

            return $this->redirectToRoute('webinar_detail', ['slug' => $webinar->getSlug()]);
        }
        $options = [];
        if ($webinar->getUsePartnerApiKeys() && !empty($webinar->getPartners()->toArray())) {
            foreach ($webinar->getPartners() as $partner) {

                if (!is_null($partner->getZoomApiKey()) && !is_null($partner->getZoomClientSecret())) {
                    $options = [
                        'apiKey' => $partner->getZoomApiKey(),
                        'apiSecret' => $partner->getZoomClientSecret(),
                    ];
                    break;
                }
            }
        }

        $zoomClient = new ZoomClient($container, $options);
        $meetingId = $webinar->getMeetingId();
        $response = $zoomClient->doRequest('GET', "/meetings/$meetingId/invitation");

        //todo handle response codes
        if (array_key_exists('invitation', $response)) {

            $email = $this->buildZoomInviteEmailToUser($user, $webinar, $translator, $response['invitation']);
//            $mailer->send($email);

            $questions = $zoomRequestHelper->parseMeetingQuestions($zoomClient, $meetingId);
            if (!empty($questions)) {
                $jsonUser = $this->appendAnswersToUser($zoomRequestHelper, $user, $questions);
            } else {
                $jsonUser = $this->getUserAsJson($user);
            }

            $meetingRegisterResponse = $zoomClient->doRequest('POST', "/meetings/$meetingId/registrants",
                [],
                [],
                $jsonUser);

            $this->persistItemRegistration($webinar, $user);

            $this->addFlash('success', $translator->trans('item.webinar_email'));

            return $this->redirectToRoute('webinar_detail', ['slug' => $webinar->getSlug()]);
        } else {
            $zoomWebinarResponse = $zoomClient->doRequest('GET', "/webinars/$meetingId");
        }
        if (!empty($zoomWebinarResponse) && array_key_exists('join_url', $zoomWebinarResponse)) {

            $startDate = $zoomRequestHelper->getZoomWebinarStartDate($zoomWebinarResponse);

            if ($startDate > new \DateTime('now')) {

                $zoomWebinarRegisterResponse = $zoomClient
                    ->doRequest('POST',
                        "/webinars/$meetingId/registrants",
                        [],
                        [],
                        $this->getUserAsJson($user));

                if (array_key_exists('join_url', $zoomWebinarRegisterResponse)) {
                    $msg = $this->getSuccessMsg($webinar, $translator, $user, $zoomWebinarRegisterResponse, $startDate);
                    $email = $this->buildZoomInviteEmailToUser($user, $webinar, $translator, $msg);
//                    $mailer->send($email);

                    $this->persistItemRegistration($webinar, $user);
                    $registerResponse = $zoomClient->doRequest('POST', "/meetings/$meetingId/registrants");

                    $this->addFlash('success', $translator->trans('item.webinar_email'));
                } else {
                    $this->addFlash('success', $translator->trans('item.webinar_registration_error'));
                }
            }
        }

        return $this->redirectToRoute('webinar_detail', ['slug' => $webinar->getSlug()]);
    }

    public function getRegisteredUser(Webinar $webinar, $user)
    {
        if (is_null($user)) {
            return null;
        }

        return $this->getDoctrine()->getRepository(ItemRegistration::class)
            ->findOneBy(['userId' => $user->getId(), 'itemId' => $webinar->getId()]);
    }

    public function getSuccessMsg(Webinar             $webinar,
                                  TranslatorInterface $translator,
                                  UserInterface       $user,
                                                      $zoomWebinarRegisterResponse,
                                                      $startDate
    )
    {
        $msg = $translator->trans('item.webinar_registration_dear') . $user->getName() . '. ' .
            $translator->trans('item.webinar_registration_msg_part_one') . ' ' . $webinar->getTitle() . '.  ' .
            $translator->trans('item.webinar_registration_msg_part_two') . $zoomWebinarRegisterResponse['join_url']
            . '  ' .
            $translator->trans('item.webinar_registration_msg_part_three') . $startDate->format('Y-m-d H:i') . '.';
        if (array_key_exists('password', $zoomWebinarRegisterResponse)) {
            $msg .= 'Password - ' . $zoomWebinarRegisterResponse['password'];
        }

        return $msg;

    }

    public function persistItemRegistration($webinar, $user)
    {
        $registeredUser = new ItemRegistration();
        $registeredUser->setItemId($webinar);
        $registeredUser->setUserId($user);
        $registeredUser->setItemType();
        $this->em->persist($registeredUser);
        $this->em->flush();
    }

    public function getUserAsJson($user)
    {
        $userArr = [
            'email' => $user->getEmail(),
            'first_name' => $user->getName(),
            'last_name' => $user->getName(),
            'phone' => !is_null($user->getPhone()) ? $user->getPhone() : '',
        ];
        return json_encode($userArr);
    }

    public function appendAnswersToUser(ZoomRequestHelper $zoomRequestHelper, $user, array $questions)
    {
        $userArr = [
            'email' => $user->getEmail(),
            'first_name' => $user->getName(),
            'last_name' => $user->getName(),
            'phone' => !is_null($user->getPhone()) ? $user->getPhone() : '',
        ];
        $answers = $zoomRequestHelper->getAnswersForRequiredQuestions($questions);
        $userArr['custom_questions'] = $answers;

        return json_encode($userArr);
    }

    public function buildZoomInviteEmailToUser($user, Webinar $webinar, TranslatorInterface $translator, string $msg): TemplatedEmail
    {
        return (new TemplatedEmail())
            ->from(new Address('support@agro.com', 'Agro Portal Bot'))
            ->to($user->getEmail())
            ->subject($translator->trans('item.webinar_email_subject') . ' ' . $webinar->getTitle())
            ->htmlTemplate('webinar/email.html.twig')
            ->context([
                'invitation' => $msg,
            ]);
    }
}
