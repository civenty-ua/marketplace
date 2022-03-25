<?php

namespace App\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Service\MailSender\Provider\MailSenderProviderInterface;
use App\Event\Item\{
    RequestEvent as ItemRequestEvent,
    VideoInteractionEvent,
};
use App\Entity\{
    Course,
    CourseUserProgress,
    Lesson,
    LessonModule,
    User,
    VideoItem,
    VideoItemWatching,
};
/**
 * Items subscriber class.
 *
 * Handle all events, connected with entities.
 */
class ItemSubscriber implements EventSubscriberInterface
{
    private const SESSION_KEY_USER_VISITED_PAGES        = 'USER_VISITED_PAGES';
    private const VIDEO_WATCHING_PROGRESS_EDGE_DEFAULT  = 50;

    private EntityManagerInterface      $entityManager;
    private SessionInterface            $session;
    private TokenStorageInterface       $tokenStorage;
    private ParameterBagInterface       $parameterBag;
    private MailSenderProviderInterface $mailSender;
    private TranslatorInterface         $translator;
    private UrlGeneratorInterface       $urlGenerator;
    /**
     * Constructor.
     *
     * @param   EntityManagerInterface      $entityManager  Entity manager.
     * @param   SessionInterface            $session        User session.
     * @param   TokenStorageInterface       $tokenStorage   Token storage.
     * @param   ParameterBagInterface       $parameterBag   Parameters storage.
     * @param   MailSenderProviderInterface $mailSender     Mail sender.
     * @param   TranslatorInterface         $translator     Translation service.
     * @param   UrlGeneratorInterface       $urlGenerator   URL generator.
     */
    public function __construct(
        EntityManagerInterface      $entityManager,
        SessionInterface            $session,
        TokenStorageInterface       $tokenStorage,
        ParameterBagInterface       $parameterBag,
        MailSenderProviderInterface $mailSender,
        TranslatorInterface         $translator,
        UrlGeneratorInterface       $urlGenerator
    ) {
        $this->entityManager    = $entityManager;
        $this->session          = $session;
        $this->tokenStorage     = $tokenStorage;
        $this->parameterBag     = $parameterBag;
        $this->mailSender       = $mailSender;
        $this->translator       = $translator;
        $this->urlGenerator     = $urlGenerator;
    }
    /**
     * Item request event handler.
     *
     * @param   ItemRequestEvent $event     Event.
     *
     * @return  void
     */
    public function onItemRequest(ItemRequestEvent $event): void
    {
        $userVisitedPages   = $this->session->get(self::SESSION_KEY_USER_VISITED_PAGES) ?? [];
        $requestUri         = $event->getRequest()->getUri();

        if (!isset($userVisitedPages[$requestUri])) {
            $event->getItem()->increaseViewsAmount();
            $this->entityManager->flush();
            $userVisitedPages[$requestUri] = true;
        }

        $this->session->set(self::SESSION_KEY_USER_VISITED_PAGES, $userVisitedPages);
    }
    /**
     * Video interaction event handler.
     *
     * @param   VideoInteractionEvent $event    Event.
     *
     * @return  void
     */
    public function onVideoInteractionEvent(VideoInteractionEvent $event): void
    {
        /** @var User|null $user */
        $user = $this->tokenStorage->getToken()
            ? $this->tokenStorage->getToken()->getUser()
            : null;
        if (!$user) {
            return;
        }
        /** @var VideoItemWatching $videoWatching */
        $videoWatching = $this->entityManager
            ->getRepository(VideoItemWatching::class)
            ->findOneBy([
                'videoItem' => $event->getVideo(),
                'user'      => $user,
            ]) ?? (new VideoItemWatching())
            ->setVideoItem($event->getVideo())
            ->setUser($user);

        $videoWatching->addWatched($event->getWatchDuration());
        $this->entityManager->persist($videoWatching);
        $this->entityManager->flush();

        $courses = $this->getCoursesByVideo($event->getVideo());
        foreach ($courses as $course) {
            $this->controlUserCourseProgress($course, $user);
        }
    }
    /**
     * Listened events registration point.
     *
     * @return string[][]                   Listened events list.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ItemRequestEvent::class         => ['onItemRequest'],
            VideoInteractionEvent::class    => ['onVideoInteractionEvent'],
        ];
    }
    /**
     * Find all courses, witch use video.
     *
     * @param   VideoItem $video            Video.
     *
     * @return  Course[]                    Courses.
     */
    private function getCoursesByVideo(VideoItem $video): array
    {
        /** @var Lesson[] $lessons */
        $lessons    = $this->entityManager
            ->getRepository(Lesson::class)
            ->findBy([
                'active'    => true,
                'videoItem' => $video,
            ]);
        $result     = [];

        foreach ($lessons as $lesson) {
            $lessonCourses = [];

            if ($lesson->getCourse()) {
                $lessonCourses[] = $lesson->getCourse();
            }
            if ($lesson->getLessonModule() && $lesson->getLessonModule()->getCourse()) {
                $lessonCourses[] = $lesson->getLessonModule()->getCourse();
            }

            foreach ($lessonCourses as $course) {
                $result[$course->getId()] = $course;
            }
        }

        return array_values($result);
    }
    /**
     * Run course user progress controlling process.
     *
     * @param   Course  $course             Course.
     * @param   User    $user               User.
     *
     * @return  void
     */
    private function controlUserCourseProgress(Course $course, User $user): void
    {
        /** @var VideoItem[] $videos */
        $fullDuration       = 0;
        $watchedDuration    = 0;
        $progressEdgeValue  = (int) $this->parameterBag->get('app.course.videoWatchingProgressEdge');
        $progressEdge       = $progressEdgeValue < 100 ?
            $progressEdgeValue
            : self::VIDEO_WATCHING_PROGRESS_EDGE_DEFAULT;
        $videos             = [];

        foreach ($course->getCourseParts() as $coursePart) {
            if ($coursePart instanceof Lesson && $coursePart->getVideoItem()) {
                $videos[$coursePart->getVideoItem()->getId()] = $coursePart->getVideoItem();
            } elseif ($coursePart instanceof LessonModule) {
                foreach ($coursePart->getLessons() as $lesson) {
                    if ($lesson->getVideoItem()) {
                        $videos[$lesson->getVideoItem()->getId()] = $lesson->getVideoItem();
                    }
                }
            }
        }
        /** @var VideoItemWatching[] $videosWatching */
        $videosWatching = $this->entityManager
            ->getRepository(VideoItemWatching::class)
            ->findBy([
                'videoItem' => array_keys($videos),
                'user'      => $user,
            ]);

        foreach ($videos as $video) {
            $fullDuration += $video->getVideoDuration() ?? 0;
        }
        foreach ($videosWatching as $watching) {
            $watchedDuration += $watching->getWatched() ?? 0;
        }

        $watchedPercent = ($watchedDuration * 100) / $fullDuration;

        if ($watchedPercent >= $progressEdge) {
            $this->markUserCourseAsCompleted($course, $user);
        }
    }
    /**
     * Mark course for user as completed.
     *
     * @param   Course  $course             Course.
     * @param   User    $user               User.
     *
     * @return  void
     */
    private function markUserCourseAsCompleted(Course $course, User $user): void
    {
        /** @var CourseUserProgress $courseProgress */
        $courseProgress = $this->entityManager
            ->getRepository(CourseUserProgress::class)
            ->findOneBy([
                'course'    => $course,
                'user'      => $user,
            ]) ?? (new CourseUserProgress())
            ->setCourse($course)
            ->setUser($user);

        if (!$courseProgress->getCompleted()) {
            $courseProgress->setCompleted(true);
        }
        if (!$courseProgress->getNotified() && $user->getEmail()) {
            $courseProgress->setNotified(true);
            $this->courseCompleteSendEmail($course, $user);
        }

        $this->entityManager->persist($courseProgress);
        $this->entityManager->flush();
    }
    /**
     * Send user email about course completing.
     *
     * @param   Course  $course             Course.
     * @param   User    $user               User.
     *
     * @return  void
     */
    private function courseCompleteSendEmail(Course $course, User $user): void
    {
        if (!$course->getFeedbackForm()) {
            return;
        }

        $titleTemplate  = $this->translator->trans('email.item_feedback.title');
        $itemType       = $this->translator->trans('email.item_feedback.types.course');
        $itemName       = $course->getTitle();
        $title          = str_replace(
            [
                '%type%',
                '%name%',
            ],
            [
                $itemType,
                $itemName,
            ],
            $titleTemplate
        );

        $this->mailSender->send(
            $user->getEmail(),
            $title,
            'email/course-complete.html.twig',
            [
                'course'        => $course,
                'feedbackLink'  => $this->urlGenerator->generate(
                    'item_feedback_form',
                    [
                        'slug' => $course->getSlug(),
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            ]
        );
    }
}