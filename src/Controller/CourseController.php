<?php

namespace App\Controller;

use App\Helper\SeoHelper;
use App\Repository\UserLastLessonViewedRepository;
use Throwable;
use Doctrine\ORM\{
    EntityManagerInterface,
    NonUniqueResultException,
    NoResultException,
};
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\{RedirectResponse, Request, Response, JsonResponse};
use Symfony\Component\Security\Core\{
    User\UserInterface,
    Security,
};
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Form\CommentType;
use App\Service\{ItemRatingService, SeoService, UserHistoryManager};
use App\Entity\{Category,
    Comment,
    Course,
    Item,
    ItemRegistration,
    Lesson,
    LessonModule,
    Page,
    Tags,
    User,
    UserLastLessonViewed,
    VideoItemWatching
};

/**
 * Class CourseController
 * @package App\Controller
 */
class CourseController extends AbstractController
{
    private const COMMENTS_PAGE_SIZE = 5;

    private Security $security;
    private EntityManagerInterface $em;
    private ItemRatingService $itemRating;

    public function __construct(Security $security, EntityManagerInterface $em, ItemRatingService $itemRating)
    {
        $this->security = $security;
        $this->em = $em;
        $this->itemRating = $itemRating;
    }

    /**
     * @Route("/course/{slug}", name="course_detail")
     *
     * @ParamConverter("course", class="App\Entity\Course")
     *
     * @throws \Exception
     */
    public function detail(
        Course              $course,
        Request             $request,
        UserHistoryManager  $userHistoryManager,
        SeoService          $seoService,
        ?UserInterface      $user,
        TranslatorInterface $translator
    ): Response
    {
        if ($request->isXmlHttpRequest()) {
            if (!$user) {
                return new JsonResponse([
                    'title' => $translator->trans('item.course_estimation_login'),
                    'login' => $this->generateUrl('login')
                ], 401);
            }
            return $this->render('course/tabs/program.html.twig', [
                'course' => $course,
                'ajaxFlag' => true
            ]);
        }
        $response = $this->redirectToLastViewedLesson($course, $userHistoryManager);
        if ($response) {
            return $response;
        }

        return $this->getView($course, $request, $userHistoryManager, $seoService);
    }

    /**
     * @Route("/course/{slug}/lesson/{lesson_id}", name="lesson_detail")
     *
     * @ParamConverter("course", class="App\Entity\Course")
     * @ParamConverter("lesson", options={"mapping": {"lesson_id": "id"}}, class="App\Entity\Lesson")
     *
     * @throws \Exception
     */
    public function detailLesson(
        Course             $course,
        Lesson             $lesson,
        Request            $request,
        UserHistoryManager $userHistoryManager,
        SeoService         $seoService
    ): Response
    {
        $this->createOrUpdateUserLastLessonViewed($course, $lesson);
        return $this->getView($course, $request, $userHistoryManager, $seoService, $lesson, 'lesson');
    }

    /**
     * @param Course $course
     * @param Request $request
     * @param UserHistoryManager $userHistoryManager
     * @param Lesson|null $lesson
     * @param string $type
     *
     * @return Response
     * @throws \Exception
     */
    private function getView(
        Course             $course,
        Request            $request,
        UserHistoryManager $userHistoryManager,
        SeoService         $seoService,
        Lesson             $lesson = null,
        string             $type = 'course'
    ): Response
    {
        $user = $this->security->getUser();
        $categories = $this->getDoctrine()->getRepository(Category::class)->findActiveCategories();
        $topItems = $this->getDoctrine()->getRepository(Item::class)->getTopItems($course->getId());
        $pagesList = $this->getDoctrine()->getRepository(Page::class)->findPageByTypeName('business_tools');
        $review = $this->itemRating->getReviews($course);
        $estimation = $this->itemRating->updateRating($course);
        $this->em->flush();
        $showSuccessRegistrationMsgBlock = null;
        if ($user) {
            $userHistoryManager->viewCourse($user, $course);

            $itemRegister = $userHistoryManager->getRegisteredUser($course, $user);
            $itemRegister ? $register = true : $register = false;
            $showSuccessRegistrationMsgBlock = $userHistoryManager->isShownSuccessRegisteredMessageBlock($course, $user);
        } else {
            $register = false;
        }

        $isExpected = false;
        if ($course->getStartDate() < new \DateTime('now')) {
            $isExpected = true;
        }

        try {
            $countRegisteredUser = $this->getDoctrine()->getRepository(ItemRegistration::class)
                ->getCountUserInCourse($course);
        } catch (NoResultException|NonUniqueResultException $e) {
            $countRegisteredUser = 0;
        }

        $activeTabAvailableValues = [
            'course',
            'program',
        ];
        $activeTabIncomeValue = $request->query->get('activeTab');
        $activeTab = in_array($activeTabIncomeValue, $activeTabAvailableValues)
            ? $activeTabIncomeValue
            : null;

        /** @var Comment[] $comments */
        $commentsRepository = $this
            ->getDoctrine()
            ->getRepository(Comment::class);
        $commentsFilter = [
            'item' => $course->getId(),
        ];
        $comments = $commentsRepository->findBy(
            $commentsFilter,
            ['createdAt' => 'DESC'],
            self::COMMENTS_PAGE_SIZE
        );
        $commentsTotalCount = $commentsRepository->getCount($commentsFilter);

        $seo = $seoService
            ->setPage(SeoHelper::PAGE_COURSE)
            ->getSeo(['title' => $course->getTitle(), 'category' => $course->getCategory()])
        ;

        $lastModified = SeoHelper::formatLastModified($course->getUpdatedAt());

        $response = new Response();
        $response->headers->set('Last-Modified', $lastModified);

        return $this->render('course/detail.html.twig', [
            'seo' => $seoService->merge($seo, $course->getSeo()),
            'categories' => $categories,
            'topTags' => $course->getTags(),
            'tagTitleFlag' => true,
            'course' => $course,
            'topItems' => $topItems,
            'voted' => $estimation['voted'],
            'rate' => $estimation['rate'],
            'countRegisteredUser' => $countRegisteredUser,
            'review' => $review,
            'type' => $type,
            'lesson' => $lesson,
            'register' => $register,
            'isExpected' => $isExpected,
            'showSuccessRegistrationMessageBlock' => $showSuccessRegistrationMsgBlock,
            'pagesList' => $pagesList,
            'activeTab' => $activeTab,
            'user' => $user,
            'comments' => [
                'exist' => $comments,
                'form' => $this->createForm(CommentType::class)->createView(),
                'pageSize' => self::COMMENTS_PAGE_SIZE,
                'totalCount' => $commentsTotalCount,
            ],
        ], $response);
    }

    /**
     * @Route ("/course/{slug}/register", name="course_register")
     *
     * @param Course $course
     * @param MailerInterface $mailer
     * @param TranslatorInterface $translator
     *
     * @return JsonResponse
     */
    public function register(Course $course, MailerInterface $mailer, TranslatorInterface $translator): JsonResponse
    {
        /** @var User $user */
        $user = $this->security->getUser();
        if (!$user) {
            return new JsonResponse([
                'status' => Response::HTTP_UNAUTHORIZED,
                'title' => $translator->trans('course.title_info'),
                'message' => $translator->trans('course.need_login'),
            ]);
        }
        if (is_null($course)) {
            return new JsonResponse([
                'status' => 'error',
                'title' => $translator->trans('course.title_error'),
                'message' => $translator->trans('course.not_course'),
            ]);
        }
        $registeredUser = $this->getDoctrine()->getRepository(ItemRegistration::class)
            ->findOneBy(['userId' => $user->getId(), 'itemId' => $course->getId()]);
        if ($registeredUser != null) {
            return new JsonResponse([
                'status' => 'error',
                'title' => $translator->trans('course.title_info'),
                'message' => $translator->trans('course.you_register'),
            ]);
        }
        $registeredUser = new ItemRegistration();
        $registeredUser->setItemId($course);
        $registeredUser->setUserId($user);
        $registeredUser->setItemType();
        $course->increaseViewsAmount();
        $this->em->persist($registeredUser);
        $this->em->flush();

        if ($course->getStartDate() > new \DateTime('now')) {
            $message = $translator->trans('course.register_success') . '"' . $course->getTitle() . '"'
                . $translator->trans('course.will_be_available') . $course->getStartDate()->format('Y-m-d H:i');
        } else {
            $message = $translator->trans('course.register_success');
            $firstLessonUrl = $this->generateUrl('lesson_detail', [
                'slug' => $course->getSlug(),
                'lesson_id' => $this->getFirstLessonId($course)
            ]);
        }

        return new JsonResponse([
            'status' => 'ok',
            'title' => $translator->trans('course.title_ok'),
            'message' => $message,
            'url' => $firstLessonUrl ?? ''
        ]);
    }

    /**
     * @Route("/lesson/{id}/interaction/log", name="lesson_interaction_log")
     *
     * @ParamConverter("lesson", class="App\Entity\Lesson")
     *
     * @param Request $request
     * @param UserInterface $user
     * @param Lesson $lesson
     *
     * @return JsonResponse
     */
    public function logInteraction(Request $request, UserInterface $user, Lesson $lesson): JsonResponse
    {
        try {
            if (!$request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'message' => 'Bad request',
                ], 400);
            }

            $video = $lesson->getVideoItem();
            $id = $lesson->getId();
            if (!$video) {
                return new JsonResponse([
                    'message' => "Lesson $id has no video",
                ], 404);
            }

            $watchDuration = (int)($request->toArray()['duration'] ?? 0);
            if ($watchDuration < 0) {
                return new JsonResponse([
                    'message' => 'watched duration can not be negative',
                ], 400);
            }
            /**
             * @var VideoItemWatching $videoWatching
             * @var User $user
             */
            $videoWatching = $this
                    ->getDoctrine()
                    ->getRepository(VideoItemWatching::class)
                    ->findOneBy([
                        'videoItem' => $video,
                        'user' => $user,
                    ]) ?? (new VideoItemWatching())
                    ->setVideoItem($video)
                    ->setUser($user);

            $videoWatching->addWatched($watchDuration);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($videoWatching);
            $entityManager->flush();

            return new JsonResponse([
                'message' => 'success',
            ], 200);
        } catch (Throwable $exception) {
            return new JsonResponse([
                'message' => "server error: {$exception->getMessage()}",
            ], 500);
        }
    }

    private function getFirstLessonId(Course $course)
    {
        foreach ($course->getCoursePartsSort() as $coursePart) {
            if ($coursePart instanceof Lesson) {
                return $coursePart->getId();
            } elseif ($coursePart instanceof LessonModule) {
                foreach ($coursePart->getLessonsSort() as $lesson) {
                    return $lesson->getId();
                }
            }
        }
    }

    private function redirectToLastViewedLesson(Course $course, UserHistoryManager $userHistoryManager): ?Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            return null;
        }
        $itemRegister = $userHistoryManager->getRegisteredUser($course, $user);
        if (!$itemRegister) {
            return null;
        }
        $userLastLessonViewed = $this->em->getRepository(UserLastLessonViewed::class)->findOneBy(['user' => $user, 'course' => $course]);
        if ($userLastLessonViewed && $userLastLessonViewed->getLesson()->getActive()) {
            return $this->redirectToRoute('lesson_detail', [
                'slug' => $course->getSlug(),
                'lesson_id' => $userLastLessonViewed->getLesson()->getId()
            ]);
        } else {
            return null;
        }
    }

    private function createOrUpdateUserLastLessonViewed(Course $course, Lesson $lesson)
    {

        $user = $this->security->getUser();
        if (!$user) {
            return;
        }
        $userLastLessonViewed = $this->em->getRepository(UserLastLessonViewed::class)->findOneBy(['user' => $user, 'course' => $course]);
        if (!$userLastLessonViewed) {
            $userLastLessonViewed = new UserLastLessonViewed();
            $userLastLessonViewed->setUser($user);
            $userLastLessonViewed->setCourse($course);
            $userLastLessonViewed->setLesson($lesson);
            $this->em->persist($userLastLessonViewed);
            $this->em->flush();

        } elseif ($userLastLessonViewed->getLesson() !== $lesson) {
            $userLastLessonViewed->setLesson($lesson);
            $this->em->flush();
        }
    }
}
