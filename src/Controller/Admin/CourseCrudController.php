<?php

namespace App\Controller\Admin;

use App\Repository\CoursePartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\{
    TextareaType,
    TextType,
};
use EasyCorp\Bundle\EasyAdminBundle\Exception\EntityNotFoundException;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Config\{
    Action,
    Actions,
    Filters,
    Crud,
};
use EasyCorp\Bundle\EasyAdminBundle\Field\{AssociationField,
    BooleanField,
    DateField,
    DateTimeField,
    IdField,
    ImageField,
    IntegerField,
    NumberField,
    SlugField,
    TextField};
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\HttpFoundation\{Request, RequestStack, Response};
use App\Form\Field\EmptyType;
use App\Admin\Field\{
    EmptyField,
    TranslationField,
};
use App\Service\{
    ExportService,
    FileManager\FileManagerInterface,
};
use App\Entity\{Comment, Course, CoursePart, CoursePartSort, ItemRegistration};

class CourseCrudController extends BaseCrudController
{
    private AdminUrlGenerator       $urlGenerator;
    private FileManagerInterface    $fileManager;
    private EntityManagerInterface  $entityManager;
    private TranslatorInterface     $translator;
    private ?string $currentLocale;

    public function __construct(
        AdminUrlGenerator       $urlGenerator,
        FileManagerInterface    $fileManager,
        EntityManagerInterface  $entityManager,
        TranslatorInterface     $translator,
        RequestStack  $requestStack

    ) {
        $this->urlGenerator     = $urlGenerator;
        $this->fileManager      = $fileManager;
        $this->entityManager    = $entityManager;
        $this->translator       = $translator;
        $this->currentLocale = $requestStack->getCurrentRequest()->getLocale();
    }

    public static function getEntityFqcn(): string
    {
        return Course::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $fieldsConfig = [
            'title' => [
                'field_type' => TextType::class,
                'required' => true,
                'label' => 'Заголовок',
                'row_attr' => [
                    'class' => 'col-md-4',
                ],
                'block_name' => 'title',
                'block_prefix' => 'translate',
            ],
            'metaTitle' => [
                'field_type' => TextType::class,
                'required' => false,
                'label' => 'Метатег title',
                'row_attr' => [
                    'class' => 'col-md-4',
                ],
            ],
            'keywords' => [
                'field_type' => TextType::class,
                'required' => false,
                'label' => 'Метатег кeywords',
                'row_attr' => [
                    'class' => 'col-md-4',
                ],
            ],
            'description' => [
                'field_type' => TextareaType::class,
                'required' => false,
                'label' => 'Метатег description',
                'row_attr' => [
                    'class' => 'col-md-4 item-meta-description',
                ],
                'attr' => [
                    'class' => 'item-meta-description',
                ],
            ],
            'short' => [
                'field_type' => TextareaType::class,
                'required' => false,
                'label' => 'Анонс',
                'row_attr' => [
                    'class' => 'col-md-8 float-right padding-left-short',
                ],
                'attr' => [
                    'class' => 'item-short'
                ]
            ],
            'content' => [
                'field_type' => CKEditorType::class,
                'required' => false,
                'label' => 'Текст',
                'row_attr' => [
                    'class' => 'col-md-12 float',
                ],
            ],
            '_empty' => [
                'field_type' => EmptyType::class,
                'required' => false,
                'mapped' => false,
                'label' => ' ',
            ],
            'descriptionTwo' => [
                'field_type' => CKEditorType::class,
                'required' => false,
                'label' => 'Опис 2',
                'row_attr' => [
                    'class' => 'col-md-12 float',
                ],
            ],
        ];

        $kernelDir = $this->get('parameter_bag')->get('kernel.project_dir');
        $imagesDir = $this->get('parameter_bag')->get('app.entity.files.course');
        (new Filesystem())->mkdir($kernelDir . $imagesDir);

        //Form
        yield TranslationField::new('translations', 'Переклади', $fieldsConfig)
            ->setRequired(true)
            ->hideOnIndex();
        $img = ImageField::new('imageName')
            ->setUploadDir('public/upload/course')
            ->setColumns('col-sm-6 col-md-4 hide-delete-btn')
            ->setUploadedFileNamePattern('[timestamp]-[contenthash].[extension]')
            ->setBasePath('upload/course')
            ->onlyOnForms()
            ->setLabel('Зображення');
        if (Crud::PAGE_NEW === $pageName) {
            yield $img->setRequired(true);
        } else {
            yield $img->setRequired(false);
        }
        yield DateTimeField::new('startDate')
            ->setFormat('yyyy-mm-dd HH:mm')
            ->onlyOnForms()
            ->renderAsNativeWidget()
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('Дата початку')
            ->setRequired(false);

        yield BooleanField::new('isActive')
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('Активна')
            ->onlyOnForms();

        yield SlugField::new('slug')
            ->setTargetFieldName('slug')
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('Аліас URL')
            ->onlyOnForms()
            ->setRequired(false);


            yield AssociationField::new('courseParts')
                ->setColumns('col-sm-6 col-md-4')
                ->setLabel('Уроки або модулі')
                ->setFormTypeOptions([
                    'by_reference' => false,
                    'query_builder' => function (CoursePartRepository $repository)  {
                        return $repository
                            ->createQueryBuilder('cp')
                            ->leftJoin('cp.coursePartSorts', 's')
                            ->orderBy('s.sort','ASC');
                    }
                ])->onlyOnForms();


        yield BooleanField::new('top')
            ->setLabel('Показувати на головній сторінці')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms();
        yield AssociationField::new('category')
            ->setLabel('Категорія')
            ->setColumns('col-sm-6 col-md-4')
            ->setFormTypeOptions([
                'query_builder' => function(EntityRepository $repository) {
                    return $repository
                        ->createQueryBuilder('category')
                        ->leftJoin('category.translations', 'categoryTranslations')
                        ->where('categoryTranslations.locale = :locale')
                        ->setParameter('locale',  $this->currentLocale)
                        ->orderBy('categoryTranslations.name', 'ASC');
                },
            ])
            ->hideOnIndex();
        yield AssociationField::new('tags')
            ->onlyOnForms()
            ->setLabel('Теги')
            ->setColumns('col-sm-6 col-md-4')
            ->setFormTypeOptions([
                'by_reference' => false,
                'query_builder' => function(EntityRepository $repository) {
                    return $repository
                        ->createQueryBuilder('tags')
                        ->leftJoin('tags.translations', 'tagsTranslations')
                        ->where('tagsTranslations.locale = :locale')
                        ->setParameter('locale',  $this->currentLocale)
                        ->orderBy('tagsTranslations.name', 'ASC');
                },
            ]);
        yield BooleanField::new('commentsAllowed')
            ->onlyOnForms()
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('Дозволені коментарі');
        yield AssociationField::new('experts')
            ->onlyOnForms()->setLabel('Експерти')
            ->setColumns('col-sm-6 col-md-4')
            ->setFormTypeOptions([
                'by_reference' => false,
                'query_builder' => function(EntityRepository $repository) {
                    return $repository
                        ->createQueryBuilder('experts')
                        ->leftJoin('experts.translations', 'expertsTranslations')
                        ->where('expertsTranslations.locale = :locale')
                        ->setParameter('locale',  $this->currentLocale)
                        ->orderBy('expertsTranslations.name', 'ASC');
                },
            ]);
        yield AssociationField::new('partners')
            ->onlyOnForms()
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('Партнери')
            ->setFormTypeOptions([
                'by_reference' => false,
                'query_builder' => function(EntityRepository $repository) {
                    return $repository
                        ->createQueryBuilder('partners')
                        ->leftJoin('partners.translations', 'partnersTranslations')
                        ->where('partnersTranslations.locale = :locale')
                        ->setParameter('locale',  $this->currentLocale)
                        ->orderBy('partnersTranslations.name', 'ASC');
                },
            ]);
        yield BooleanField::new('registration_required')
            ->setLabel("Обов'язкова реєстрація")
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms();
        yield AssociationField::new('crops')
            ->onlyOnForms()->setLabel('Культури')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms()
            ->setFormTypeOptions([
                'by_reference' => false,
                'query_builder' => function(EntityRepository $repository) {
                    return $repository
                        ->createQueryBuilder('crop')
                        ->leftJoin('crop.translations', 'cropTranslations')
                        ->where('cropTranslations.locale = :locale')
                        ->setParameter('locale',  $this->currentLocale)
                        ->orderBy('cropTranslations.name', 'ASC');
                },
            ]);
        yield AssociationField::new('feedbackForm')
            ->setLabel('Форма зворотного зв’язку')
            ->setColumns('col-sm-6 col-md-4')
            ->hideOnIndex();
        yield BooleanField::new('personalConsalting')
            ->setLabel('Персональна консультація')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms();
        yield NumberField::new('rating')
            ->setLabel('course.rating')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms();
        yield NumberField::new('oldUserCount')
            ->setLabel('course.old_user')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms();

        yield IntegerField::new('viewsAmount')
            ->setLabel('Кількість переглядів')
            ->hideOnIndex()
            ->setColumns('col-sm-6 col-md-4');
        yield AssociationField::new('bannerCategories')
            ->onlyOnForms()->setLabel('Є баннером в категоріях')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms()
            ->setFormTypeOptions([
                'by_reference' => false,
                'query_builder' => function(EntityRepository $repository) {
                    return $repository
                        ->createQueryBuilder('bannerCategories')
                        ->leftJoin('bannerCategories.translations', 'bannerCategoriesTranslations')
                        ->where('bannerCategoriesTranslations.locale = :locale')
                        ->setParameter('locale',  $this->currentLocale)
                        ->orderBy('bannerCategoriesTranslations.name', 'ASC');
                },
            ]);

        //Index
        yield IdField::new('id')
            ->onlyOnIndex();
        yield TextField::new('title')
            ->hideOnForm()
            ->setLabel('Назва');
        yield SlugField::new('slug')
            ->setTargetFieldName('slug')
            ->setLabel('Аліас URL')
            ->hideOnForm()
            ->setRequired(false);
        yield AssociationField::new('category')
            ->setLabel('Категорія')
            ->hideOnForm();
        yield ImageField::new('imageName')
            ->setBasePath('upload/course')
            ->onlyOnIndex()
            ->setLabel('Зображення');
        yield DateField::new('createdAt')
            ->onlyOnIndex()
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('Дата створення');
        yield TextField::new('commentsLink')
            ->setLabel('admin.comment.title')
            ->hideOnForm()
            ->setVirtual(true)
            ->formatValue(function ($value, Course $course) {
                $link       = $this->urlGenerator
                    ->setController(CommentCrudController::class)
                    ->setAction(Action::INDEX)
                    ->set('query', $course->getId())
                    ->generateUrl();
                $linkTitle  = '(' . $this->getDoctrine()->getRepository(Comment::class)->getCount(['item' => $course->getId()]) . ')';

                return "<a target=\"_blank\" href=\"$link\">$linkTitle</a>";
            });
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('isActive')
            ->add('category')
            ->add('partners')
            ->add('experts')
            ->add('tags')
            ->add('crops')
            ->add('createdAt')
            ->add('updatedAt');
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['translations.title'])
            ->setFormOptions(['validation_groups' => ['Default', 'creation']],
                ['validation_groups' => ['Default', 'creation']])
            ->setEntityLabelInPlural('admin.dashboard.course.list_title')
            ->setEntityLabelInSingular('admin.dashboard.course.edit_button_title')
            ->setPageTitle(Crud::PAGE_NEW, 'admin.dashboard.course.new_page_title')
            ->setPageTitle(Crud::PAGE_EDIT, 'admin.dashboard.course.edit_page_title')
            ->setFormThemes(
                [
                    '@A2lixTranslationForm/bootstrap_4_layout.html.twig',
                    '@FOSCKEditor/Form/ckeditor_widget.html.twig',
                    '@EasyAdmin/crud/form_theme.html.twig',
                ]
            )
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->add(
                Crud::PAGE_INDEX,
                Action::new('courseExport', 'Експорт')
                    ->createAsGlobalAction()
                    ->linkToCrudAction('courseExport')
                    ->addCssClass('btn btn-primary')
            )
            ->add(
                Crud::PAGE_INDEX,
                Action::new('copy', false)
                    ->linkToCrudAction('copyCourse')
                    ->setIcon('fas fa-copy')
                    ->setHtmlAttributes([
                        'placeholder' => 'Копіювати',
                    ])
            )
            ->update(Crud::PAGE_INDEX,
                Action::DELETE,
                function (Action $action) {
                    $dto = $action->getAsDto();
                    $dto->setDisplayCallable(function ($entity) {
                        $flag = $this->diplayRule($entity);

                        return $flag === true;
                    });

                    return $action;
                })
            ->update(Crud::PAGE_INDEX,
                Action::BATCH_DELETE,
                function (Action $action) {
                    $dto = $action->getAsDto();
                    $dto->setDisplayCallable(function ($entity) {
                        $flag = $this->diplayRule($entity);

                        return $flag === true;
                    });

                    return $action;
                });
    }

    /**
     * @param ExportService $exportService
     *
     * @return void
     */
    public function courseExport(ExportService $exportService): void
    {
        $query = $this->getDoctrine()
            ->getRepository('App:Course')
            ->createQueryBuilder('c')
            ->setMaxResults(1000)
            ->getQuery();

        $courseList = $query->getResult();

        $exportData = [];

        /** @var Course $course */
        foreach ($courseList as $course) {

            $expertList = [];
            foreach ($course->getExperts() as $expert) {
                $expertList[] = $expert->getName();
            }

            $partnerList = [];
            foreach ($course->getPartners() as $partner) {
                $partnerList[] = $partner->getName();
            }

            $tagList = [];
            foreach ($course->getTags() as $tag) {
                $tagList[] = $tag->getName();
            }

            $cropsList = [];
            foreach ($course->getCrops() as $crop) {
                $cropsList[] = $crop->getName();
            }
            $category = $course->getCategory();
            $exportData[] = [
                'ID' => $course->getId(),
                'Активний' => $course->getIsActive() ? 'Так' : 'Ні',
                'Назва' => $course->getTitle(),
                'Текст' => $course->getContent(),
                'Метатег title' => $course->getMetaTitle(),
                'Метатег кeywords' => $course->getKeywords(),
                'Метатег description' => $course->getDescription(),
                'Анонс' => $course->getShort(),
                'Партнери' => implode(', ', $partnerList),
                'Експерти' => implode(', ', $expertList),
                'Теги' => implode(', ', $tagList),
                'Культури' => implode(', ', $cropsList),
                'Категорія' => !is_null($category) ? $course->getCategory()->getName() : '',
                'Рейтинг' => $course->getRating(),
                'Кількість переглядів' => $course->getViewsAmount(),
                'Дата створення' => $course->getCreatedAt(),
                'Дата редагування' => $course->getUpdatedAt(),
            ];
        }

        $exportService->export('course', $exportData);
    }

    /**
     * Copy course.
     */
    public function copyCourse(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var Course|null $course */
        $courseId = (int)$request->query->get('entityId');
        $course = $this
            ->getDoctrine()
            ->getRepository(Course::class)
            ->findOneBy([
                'id' => $courseId,
            ]);

        if (!$course) {
            throw new EntityNotFoundException([
                'entity_name' => Course::class,
                'entity_id_name' => 'id',
                'entity_id_value' => $courseId,
            ]);
        }

        $courseNew = $course->getCopy(
            $this->get('parameter_bag'),
            $this->fileManager
        );
        $this->persistEntity($entityManager, $courseNew);

        $redirectUrl = $this->urlGenerator
            ->setController(self::class)
            ->setAction(Action::EDIT)
            ->setEntityId($courseNew->getId())
            ->generateUrl();

        return $this->redirect($redirectUrl);
    }

    private function diplayRule($entity)
    {
        $registered = $this->entityManager->getRepository(ItemRegistration::class)
            ->findOneBy(['itemId' => $entity->getId()]);
        $courseParts = $entity->getCourseParts();
        if (is_null($registered) && is_null($courseParts)) {
            $flag = true;
        } else {
            $flag = false;
        }

        return $flag;
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void {
        $this->saveEntityAndSort($entityManager, $entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void {
        $this->saveEntityAndSort($entityManager, $entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    private function saveEntityAndSort(EntityManagerInterface $entityManager, &$entityInstance) {
        $oldSortCoursePartsSort = $entityManager->getRepository(CoursePartSort::class)->findBy(['course' => $entityInstance]);
        foreach ($oldSortCoursePartsSort as $coursePart) {
            $entityManager->remove($coursePart);
        }
        /**
         * @var integer $key
         * @var CoursePart $entity
         */
        $sortCounter = 0;
        $coursePartSort = [];
        $courseParts = $entityInstance->getCoursePartsSort();

        foreach ($courseParts as $entity) {
            $coursePartSort[$sortCounter] = new CoursePartSort();
            $coursePartSort[$sortCounter]->setCourse($entityInstance);
            $coursePartSort[$sortCounter]->setCoursePart($entity);
            $coursePartSort[$sortCounter]->setSort($sortCounter);
            $entityManager->persist($coursePartSort[$sortCounter]);
            $sortCounter++;
        }
    }
}
