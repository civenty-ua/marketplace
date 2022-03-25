<?php

namespace App\Controller\Admin;

use Symfony\Contracts\Translation\TranslatorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Config\{
    Action,
    Actions,
    Filters,
    Crud,
};
use EasyCorp\Bundle\EasyAdminBundle\Field\{
    IdField,
    NumberField,
    TextField,
};
use App\Service\ExportService;
use App\Entity\{
    Course,
    ItemRegistration,
    Lesson,
    LessonModule,
    VideoItem,
    VideoItemWatching,
};
/**
 * Class CourseStatisticCrudController
 */
class CourseStatisticCrudController extends BaseCrudController
{
    /** @var ItemRegistration[] $registrations */
    private array $coursesRegistrations = [];

    private AdminUrlGenerator   $urlGenerator;
    private TranslatorInterface $translator;

    public function __construct(AdminUrlGenerator $urlGenerator, TranslatorInterface $translator)
    {
        $this->urlGenerator = $urlGenerator;
        $this->translator   = $translator;
    }

    public static function getEntityFqcn(): string
    {
        return Course::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->onlyOnIndex();
        yield TextField::new('title')
            ->setLabel('admin.courseStatistic.title');
        yield NumberField::new('courseTotalDuration')
            ->setLabel('admin.courseStatistic.totalDuration')
            ->onlyOnDetail()
            ->setVirtual(true)
            ->formatValue(function ($value, Course $course) {
                $totalDuration = $this->getCourseTotalDuration($course);

                return $this->getDurationPrintableValue($totalDuration);
            });
        yield NumberField::new('registrationsAmount')
            ->setLabel('admin.courseStatistic.registrationsAmount')
            ->onlyOnDetail()
            ->setVirtual(true)
            ->formatValue(function ($value, Course $course) {
                $registrations = $this->getCourseRegistrations($course);

                return count($registrations);
            });
        yield TextField::new('genderSplit')
            ->setLabel('admin.courseStatistic.gender.split')
            ->onlyOnDetail()
            ->setVirtual(true)
            ->setTemplatePath('admin/course_statistic/gender_split.html.twig')
            ->formatValue(function ($value, Course $course) {
                $registrations  = $this->getCourseRegistrations($course);
                $gendersData    = $this->getRegistrationsGendersSplitData($registrations);
                $typesLabels    = [
                    0       => 'male',
                    1       => 'female',
                    null    => 'none',
                ];
                $data           = [];

                foreach ($gendersData as $index => $value) {
                    $data[] = [
                        'type'      => $typesLabels[$index],
                        'count'     => $value,
                    ];
                }

                return $data;
            });
        yield TextField::new('regionSplit')
            ->setLabel('admin.courseStatistic.region.split')
            ->onlyOnDetail()
            ->setVirtual(true)
            ->setTemplatePath('admin/course_statistic/region_split.html.twig')
            ->formatValue(function ($value, Course $course) {
                $registrations = $this->getCourseRegistrations($course);

                return $this->getRegistrationsRegionsSplitData($registrations);
            });
        yield TextField::new('usersProgress')
            ->setLabel('admin.courseStatistic.usersProgress.title')
            ->onlyOnDetail()
            ->setVirtual(true)
            ->formatValue(function ($value, Course $course) {
                $link       = $this->urlGenerator
                    ->unsetAll()
                    ->setController(CourseRegistrationCrudController::class)
                    ->setAction(Action::INDEX)
                    ->set('query', $course->getId())
                    ->removeReferrer()
                    ->generateUrl();
                $linkTitle  = $this->translator->trans('admin.courseStatistic.usersProgress.link');

                return "<a target=\"_blank\" href=\"$link\">$linkTitle</a>";
            });
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields([
                'translations.title',
            ])
            ->setFormOptions(
                ['validation_groups' => ['Default', 'creation']],
                ['validation_groups' => ['Default', 'creation']],
            )
            ->setFormThemes([
                '@A2lixTranslationForm/bootstrap_4_layout.html.twig',
                '@FOSCKEditor/Form/ckeditor_widget.html.twig',
                '@EasyAdmin/crud/form_theme.html.twig',
            ])
            ->setEntityLabelInPlural('admin.courseStatistic.titles.index')
            ->setEntityLabelInSingular('admin.courseStatistic.titles.index')
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->remove(Crud::PAGE_INDEX, Crud::PAGE_NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->add(
                Crud::PAGE_INDEX,
                Action::new('export')
                    ->setLabel('admin.courseStatistic.actions.export')
                    ->createAsGlobalAction()
                    ->linkToCrudAction('export')
                    ->addCssClass('btn btn-primary')
            )
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX,
                Action::DETAIL,
                function (Action $action) {
                    return $action
                        ->setIcon('fas fa-eye')
                        ->setLabel(false);
                })
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT);
    }
    /**
     * @param ExportService $exportService
     *
     * @return void
     */
    public function export(ExportService $exportService): void
    {
        /** @var Course[] $courses */
        $courses    = $this->getDoctrine()
            ->getRepository(Course::class)
            ->findBy(
                [],
                ['id' => 'DESC'],
                1000
            );
        $result     = [];

        foreach ($courses as $course) {
            $titleName                  = $this->translator->trans('admin.courseStatistic.title');
            $titleTotalDuration         = $this->translator->trans('admin.courseStatistic.totalDuration');
            $titleRegistrationsAmount   = $this->translator->trans('admin.courseStatistic.registrationsAmount');
            $titleGenderSplit           = $this->translator->trans('admin.courseStatistic.gender.split');
            $titleUsersProgress         = $this->translator->trans('admin.courseStatistic.usersProgress.title');
            $courseRegistrations        = $this->getCourseRegistrations($course);
            $courseTotalDuration        = $this->getCourseTotalDuration($course);

            $result[] = [
                'ID'                        => $course->getId(),
                $titleName                  => $course->getTitle(),
                $titleTotalDuration         => $this->getDurationPrintableValue($courseTotalDuration),
                $titleRegistrationsAmount   => count($courseRegistrations),
                $titleGenderSplit           => $this->getRegistrationsGendersSplitPrintableValue($courseRegistrations),
                $titleUsersProgress         => $this->getCourseUsersProgressPrintableValue($course),
            ];
        }

        $exportService->export('course', $result);
    }
    /**
     * Get registrations "genders split" printable value.
     *
     * @param ItemRegistration[] $registrations Registrations.
     *
     * @return string Printable value.
     */
    private function getRegistrationsGendersSplitPrintableValue(array $registrations): string
    {
        $gendersAmount  = $this->getRegistrationsGendersSplitData($registrations);
        $gendersTitle   = [
            0 => $this->translator->trans('admin.courseStatistic.gender.male'),
            1 => $this->translator->trans('admin.courseStatistic.gender.female'),
            null => $this->translator->trans('admin.courseStatistic.gender.none'),
        ];
        $totalCount     = array_sum($gendersAmount);
        $percentsLeft   = 100;
        $output         = [];

        foreach ($gendersAmount as $gender => $amount) {
            $isLast = array_key_last($gendersAmount) === $gender;
            $title = $gendersTitle[$gender];
            $amountPercent = 0;

            if ($totalCount > 0) {
                if (!$isLast) {
                    $amountPercent = ceil(($amount * 100) / $totalCount);
                    $percentsLeft -= $amountPercent;
                } else {
                    $amountPercent = $percentsLeft;
                }
            }

            $output[] = "$title ($amountPercent%)";
        }

        return implode(' / ', $output);
    }
    /**
     * Get course users progress printable value.
     *
     * @param Course $course Course.
     *
     * @return string Printable value.
     */
    private function getCourseUsersProgressPrintableValue(Course $course): string
    {
        $data   = $this->getCourseUsersProgressData($course);
        $output = [];

        foreach ($data as $item) {
            $outputTitle    = $item['watchedPercent'] > 0
                ? $this->translator->trans('admin.courseStatistic.usersProgress.inProgress')
                : $this->translator->trans('admin.courseStatistic.usersProgress.progressNoN');
            $output[]       = str_replace(
                [
                    '%USER%',
                    '%TIME%',
                    '%PERCENT%',
                ],
                [
                    $item['user']->getEmail(),
                    $this->getDurationPrintableValue($item['watched']),
                    $item['watchedPercent'],
                ],
                $outputTitle
            );
        }

        return implode("\n", $output);
    }
    /**
     * Get duration printable value.
     *
     * @param   int $duration               Duration in seconds.
     *
     * @return  string                      Printable value.
     */
    private function getDurationPrintableValue(int $duration): string
    {
        return gmdate('H:i:s', $duration);
    }
    /**
     * Get course registrations.
     *
     * @param Course $course Course.
     *
     * @return ItemRegistration[] Registrations.
     */
    private function getCourseRegistrations(Course $course): array
    {
        if (!isset($this->coursesRegistrations[$course->getId()])) {
            $this->coursesRegistrations[$course->getId()] = $this
                ->getDoctrine()
                ->getRepository(ItemRegistration::class)
                ->findBy([
                    'itemId' => $course,
                ]);
        }

        return $this->coursesRegistrations[$course->getId()];
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
     * Get course total duration.
     *
     * @param   Course $course              Course.
     *
     * @return  int                         Course total duration.
     */
    private function getCourseTotalDuration(Course $course): int
    {
        $courseVideos   = $this->getCourseVideos($course);
        $totalDuration  = 0;

        foreach ($courseVideos as $courseVideo) {
            $totalDuration += $courseVideo->getVideoDuration();
        }

        return $totalDuration;
    }
    /**
     * Get registrations "genders split" data.
     *
     * @param   ItemRegistration[] $registrations   Registrations.
     *
     * @return  array                               Genders split data.
     */
    private function getRegistrationsGendersSplitData(array $registrations): array
    {
        $result = [
            1       => 0,
            0       => 0,
            null    => 0,
        ];

        foreach ($registrations as $registration) {
            if ($registration->getUserId()) {
                $result[$registration->getUserId()->getGender()]++;
            }
        }

        return $result;
    }
    /**
     * Get registrations "regions split" data.
     *
     * @param   ItemRegistration[] $registrations   Registrations.
     *
     * @return  array                               Regions split data.
     */
    private function getRegistrationsRegionsSplitData(array $registrations): array
    {
        $result = [];

        foreach ($registrations as $registration) {
            if ($registration->getUserId()) {
                $region         = $registration->getUserId()->getRegion();
                $regionId       = $region ? $region->getId()    : 0;
                $regionTitle    = $region
                    ? $region->getName()
                    : $this->translator->trans('admin.courseStatistic.region.empty');

                $result[$regionId] = $result[$regionId] ?? [
                        'title' => $regionTitle,
                        'users' => 0,
                    ];
                $result[$regionId]['users']++;
            }
        }

        return array_values($result);
    }
    /**
     * Get course users progress data.
     *
     * @param   Course $course              Course.
     *
     * @return  array                       Data.
     */
    private function getCourseUsersProgressData(Course $course): array
    {
        $registrations              = $this->getCourseRegistrations($course);
        $courseVideos               = $this->getCourseVideos($course);
        $courseVideosTotalDuration  = $this->getCourseTotalDuration($course);
        $result                     = [];

        foreach ($registrations as $registration) {
            $user           = $registration->getUserId();
            $userWatched    = 0;

            if (!$user) {
                continue;
            }

            /** @var VideoItemWatching[] $userVideosWatching */
            $userVideosWatching = $this
                ->getDoctrine()
                ->getRepository(VideoItemWatching::class)
                ->findBy([
                    'videoItem' => $courseVideos,
                    'user'      => $user,
                ]);

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

            $result[] = [
                'user'              => $user,
                'watched'           => $userWatched,
                'watchedPercent'    => $userWatchedPercent,
            ];
        }

        return $result;
    }
}