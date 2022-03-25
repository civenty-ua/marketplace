<?php

namespace App\Controller\Admin;

use App\Admin\Filter\ItemTypeFilter;
use App\Repository\ItemRepository;
use App\Service\ExportService;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\EntityFilterType;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use RuntimeException;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\{
    Action,
    Actions,
    Filters,
    Crud,
};
use EasyCorp\Bundle\EasyAdminBundle\Field\{AssociationField, DateTimeField, TextField};
use App\Entity\{
    Course,
    CourseUserProgress,
    Item,
    ItemRegistration,
    Lesson,
    LessonModule,
    User,
    VideoItem,
    VideoItemWatching,
};
/**
 * Class CourseRegistrationCrudController
 */
class CourseRegistrationCrudController extends ItemRegistrationCrudController
{
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return $this->get(EntityRepository::class)->createQueryBuilder($searchDto, $entityDto, $fields, $filters)->andWhere('entity.itemType = \'course\'');
    }
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('userId')->setLabel('Користувач'))
            ->add(EntityFilter::new('itemId')->setLabel('Курс'))
            ->add('createdAt');
    }

    /**
     * @inheritDoc
     */
    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('userId')
            ->setLabel('admin.courseRegistration.user');
        yield TextField::new('userId.region')
            ->setLabel('admin.courseRegistration.userRegion')
            ->formatValue(function($value, ItemRegistration $registration) {
                $region = $registration->getUserId()->getRegion();

                return $region ? $region->getName() : null;
            });
        yield AssociationField::new('itemId')
            ->setLabel('admin.itemRegistration.item');
        yield TextField::new('userCourseProgress')
            ->setLabel('admin.courseRegistration.usersProgress.title')
            ->setVirtual(true)
            ->formatValue(function($value, ItemRegistration $registration) {
                return $this->getUsersProgressPrintableValue($registration);
            });
        yield TextField::new('isNotified')
            ->setLabel('admin.courseRegistration.isNotified.title')
            ->setVirtual(true)
            ->formatValue(function($value, ItemRegistration $registration) {
                try {
                    $courseProgress = $this->getCourseUserProgress($registration);

                    return $courseProgress && $courseProgress->getNotified()
                        ? $this->translator->trans('admin.courseRegistration.isNotified.yes')
                        : $this->translator->trans('admin.courseRegistration.isNotified.no');
                } catch (RuntimeException $exception) {
                    return $this->translator->trans('admin.courseRegistration.isNotified.no');
                }
            });
        yield DateTimeField::new('createdAt')
            ->setLabel('admin.itemRegistration.createdDate');
        yield TextField::new('userLeftFeedback')
            ->setLabel('admin.itemRegistration.userFeedback.title')
            ->setVirtual(true)
            ->formatValue(function($value, ItemRegistration $registration) {
                $userFeedback = parent::findUserFeedback($registration);

                return $userFeedback
                    ? $this->translator->trans('admin.itemRegistration.userFeedback.exist')
                    : $this->translator->trans('admin.itemRegistration.userFeedback.notExist');
            });
        yield TextField::new('itemType')->setLabel('Тип')->onlyOnIndex();
    }
    /**
     * @inheritDoc
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setSearchFields(['itemId.id'])
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPageTitle(Crud::PAGE_INDEX, 'admin.courseRegistration.titles.index')
            ->setEntityLabelInPlural('admin.courseRegistration.titles.plural')
            ->setEntityLabelInSingular('admin.courseRegistration.titles.singular')
            ->showEntityActionsInlined();
    }

    /**
     * Send feedback form.
     */
    public function sendFeedbackForm(Request $request): Response
    {
        /** @var ItemRegistration|null $registration */
        $backUrl        = $request->query->get('referrer');
        $registrationId = (int) $request->query->get('entityId');
        $registration   = $this
            ->getDoctrine()
            ->getRepository(ItemRegistration::class)
            ->findOneBy([
                'id' => $registrationId,
            ]);
        /** @var Course|Item|null $course */
        $course = $registration->getItemId();

        if (!($course instanceof Course)) {
            $error = $this->translator->trans('admin.courseRegistration.errors.courseWasNoFound');
            $this->session->getFlashBag()->add('info', $error);

            return $this->redirect($backUrl);
        }
        if (!$course->getFeedbackForm()) {
            $error = $this->translator->trans('admin.courseRegistration.errors.courseHasNoFeedbackForm');
            $this->session->getFlashBag()->add('info', $error);

            return $this->redirect($backUrl);
        }

        try {
            $courseProgress = $this->getCourseUserProgress($registration);
            $courseProgress->setNotified(true);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($courseProgress);
            $entityManager->flush();

            $this->sendFeedbackFormEmail($course, $registration->getUserId());
        } catch (RuntimeException $exception) {

        }

        return $this->redirect($backUrl);
    }
    /**
     * Get users progress printable value.
     *
     * @param   ItemRegistration $registration  Registration.
     *
     * @return  string|null                     Printable value.
     */
    private function getUsersProgressPrintableValue(ItemRegistration $registration): ?string
    {
        /** @var Course|Item|null $course */
        $course = $registration->getItemId();
        if (!($course instanceof Course)) {
            return null;
        }
        /** @var VideoItemWatching[] $userVideosWatching */
        $courseVideos               = $this->getCourseVideos($course);
        $userVideosWatching         = $this
            ->getDoctrine()
            ->getRepository(VideoItemWatching::class)
            ->findBy([
                'videoItem' => $courseVideos,
                'user'      => $registration->getUserId(),
            ]);
        $courseVideosTotalDuration  = 0;
        $userWatched                = 0;

        foreach ($courseVideos as $courseVideo) {
            $courseVideosTotalDuration += $courseVideo->getVideoDuration();
        }

        foreach ($userVideosWatching as $watching) {
            if ($watching->getVideoItem()) {
                $watched            = $watching->getWatched();
                $videoDuration      = $watching->getVideoItem()->getVideoDuration();
                $watchedNormalized  = $watched <= $videoDuration ? $watched : $videoDuration;
                $userWatched       += $watchedNormalized;
            }
        }

        $userWatchedPercent = $courseVideosTotalDuration > 0
            ? ceil(($userWatched * 100) / $courseVideosTotalDuration)
            : 0;

        if (
            $userWatchedPercent > 100 ||
            $userWatchedPercent > 0 && $courseVideosTotalDuration - $userWatched <= 10
        ) {
            $userWatchedPercent = 100;
        }

        if ($userWatchedPercent > 0) {
            return str_replace(
                [
                    '%TIME%',
                    '%PERCENT%',
                ],
                [
                    gmdate('H:i:s', $userWatched),
                    $userWatchedPercent,
                ],
                $this->translator->trans('admin.courseRegistration.usersProgress.inProgress')
            );
        }

        return $this->translator->trans('admin.courseRegistration.usersProgress.progressNoN');
    }
    /**
     * Get course users progress printable value.
     *
     * @param   Course $course              Course.
     *
     * @return  VideoItem[]                 Video items.
     */
    private function getCourseVideos(Course $course): array
    {
        $result = [];

        foreach ($course->getCourseParts() as $coursePart) {
            if (
                $coursePart instanceof Lesson   &&
                $coursePart->getActive()        &&
                $coursePart->getVideoItem()
            ) {
                $result[] = $coursePart->getVideoItem();
            } elseif ($coursePart instanceof LessonModule) {
                foreach ($coursePart->getLessons() as $lesson) {
                    if ($lesson->getVideoItem()) {
                        $result[] = $lesson->getVideoItem();
                    }
                }
            }
        }

        return $result;
    }
    /**
     * Send feedback form email.
     *
     * @param   Course  $course             Course.
     * @param   User    $user               User.
     *
     * @return  void
     */
    private function sendFeedbackFormEmail(Course $course, User $user): void
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
    /**
     * Get course user progress by registration.
     *
     * @param   ItemRegistration $registration  Registration.
     *
     * @return  CourseUserProgress              Course user progress.
     * @throws  RuntimeException                Course was not found.
     */
    private function getCourseUserProgress(ItemRegistration $registration): CourseUserProgress
    {
        /** @var Course|Item|null $course */
        $course = $registration->getItemId();
        if (!($course instanceof Course)) {
            throw new RuntimeException();
        }
        /** @var CourseUserProgress|null $courseProgress */
        $progress = $this
            ->getDoctrine()
            ->getRepository(CourseUserProgress::class)
            ->findOneBy([
                'course'    => $course,
                'user'      => $registration->getUserId(),
            ]);

        if (!$progress) {
            $progress = (new CourseUserProgress())
                ->setCourse($course)
                ->setUser($registration->getUserId());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($progress);
            $entityManager->flush();
        }

        return $progress;
    }

    /**
     * @param ExportService $exportService
     *
     * @return void
     */
    public function export(ExportService $exportService,Request $request): void
    {
        $query = $this->getDoctrine()
            ->getRepository('App:ItemRegistration')
            ->createQueryBuilder('ir')->andWhere('ir.itemType = :item')
            ->setParameter('item','course');

        $itemRegistrationList = $query->getQuery()->toIterable();
        $exportData = $this->getExportData($itemRegistrationList);


        $exportService->export('courseRegistration', $exportData);
    }

    protected function getExportData(iterable $itemRegistrationList ):array
    {
        $exportData = [];

        /** @var ItemRegistration $itemRegistration */
        foreach ($itemRegistrationList as $itemRegistration) {
            $exportData[] = [
                'ID' => $itemRegistration->getId(),
                'Користувач' => $itemRegistration->getUserId()->getName(),
                'Регіон' => $itemRegistration->getUserId()->getRegion()
                    ? $itemRegistration->getUserId()->getRegion()->getName()
                    : null,
                'Курс' => $itemRegistration->getItemId()->getTitle(),
                'Проходження' => $this->getUsersProgressPrintableValue($itemRegistration),
                'Отримав фидбек форму форму' =>  parent::findUserFeedback($itemRegistration)
                    ? $this->translator->trans('admin.itemRegistration.userFeedback.exist')
                    : $this->translator->trans('admin.itemRegistration.userFeedback.notExist'),
                'Дата' => $itemRegistration->getCreatedAt(),
            ];
        }

        return $exportData;
    }
}