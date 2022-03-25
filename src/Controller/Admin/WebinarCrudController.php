<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\{
    AssociationField,
    BooleanField,
    DateField,
    IdField,
    ImageField,
    SlugField,
    TextField,
    IntegerField,
    DateTimeField,
    NumberField,
};
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\HttpFoundation\{Request, RequestStack, Response};
use App\Admin\Field\TranslationField;
use App\Service\{
    ExportService,
    FileManager\FileManagerInterface,
};
use App\Form\Field\EmptyType;
use App\Entity\Webinar;

class WebinarCrudController extends BaseCrudController
{
    private AdminUrlGenerator $urlGenerator;
    private FileManagerInterface $fileManager;
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;
    private ?string $currentLocale;


    public function __construct(
        AdminUrlGenerator      $urlGenerator,
        FileManagerInterface   $fileManager,
        EntityManagerInterface $entityManager,
        TranslatorInterface    $translator,
        RequestStack  $requestStack

    ) {
        $this->urlGenerator = $urlGenerator;
        $this->fileManager = $fileManager;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->currentLocale = $requestStack->getCurrentRequest()->getLocale();

    }

    public static function getEntityFqcn(): string
    {
        return Webinar::class;
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
            'descriptionTwo' => [
                'field_type' => CKEditorType::class,
                'required' => false,
                'label' => 'Опис 2',
                'row_attr' => [
                    'class' => 'col-md-12 float',
                ],
            ],
        ];

        yield TranslationField::new('translations', 'Переклади', $fieldsConfig)
            ->setRequired(true)
            ->hideOnIndex()
            ->addCssClass('col-md-12 float');

        $kernelDir = $this->get('parameter_bag')->get('kernel.project_dir');
        $imagesDir = $this->get('parameter_bag')->get('app.entity.files.webinar');
        (new Filesystem())->mkdir($kernelDir . $imagesDir);

        //Form
        yield ImageField::new('imageName')
            ->setUploadDir('public/upload/webinar')
            ->setUploadedFileNamePattern('[timestamp]-[contenthash].[extension]')
            ->setBasePath('upload/webinar')
            ->onlyOnForms()
            ->setColumns('col-sm-6 col-md-4 hide-delete-btn')
            ->setLabel('Зображення')
            ->setRequired(Crud::PAGE_NEW === $pageName);
        yield DateTimeField::new('startDate')
            ->setFormat('yyyy-mm-dd HH:mm')
            ->setColumns('col-sm-6 col-md-4')
            ->renderAsNativeWidget()
            ->hideOnIndex()
            ->setLabel('Дата початку')
            ->setRequired(false);
        yield BooleanField::new('isActive')
            ->setColumns('col-sm-6 col-md-4')
            ->hideOnIndex()
            ->setLabel('Активна');
        yield AssociationField::new('videoItem')
            ->onlyOnForms()
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('Відео')
            ->onlyOnForms()
            ->setRequired(false);
        yield SlugField::new('slug')
            ->setTargetFieldName('slug')
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('Аліас URL')
            ->onlyOnForms()
            ->setRequired(false);
        yield BooleanField::new('top')
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('Показувати на головній сторінці')
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
        yield AssociationField::new('feedbackForm')
            ->setLabel('Форма зворотного зв’язку')
            ->setColumns('col-sm-6 col-md-4')
            ->hideOnIndex();
        yield BooleanField::new('commentsAllowed')
            ->setLabel('Дозволені коментарі')
            ->setColumns('col-sm-6 col-md-4')
            ->hideOnIndex();
        yield AssociationField::new('partners')
            ->onlyOnForms()
            ->setLabel('Партнери')
            ->setColumns('col-sm-6 col-md-4')
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
        yield IntegerField::new('meetingId')
            ->setLabel('Id Конференції Zoom')
            ->setColumns('col-sm-6 col-md-4')
            ->setRequired(false)
            ->onlyOnForms()
            ->hideOnIndex();
        yield BooleanField::new('usePartnerApiKeys')
            ->setLabel('Використовувати API ключі партнера')
            ->setColumns('col-sm-6 col-md-4')
            ->hideOnIndex();
        yield AssociationField::new('experts')
            ->onlyOnForms()
            ->setLabel('Експерти')
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
        yield NumberField::new('rating')
            ->setLabel('Старий Рейтинг')
            ->setRequired(false)
            ->setColumns('col-sm-6 col-md-4')
            ->hideOnIndex();
        yield BooleanField::new('registration_required')
            ->setLabel("Обов'язкова реєстрація")
            ->onlyOnForms()
            ->setColumns('col-sm-6 col-md-4');
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
        yield AssociationField::new('crops')
            ->onlyOnForms()
            ->setLabel('Культури')
            ->setColumns('col-sm-6 col-md-4')
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
        yield IntegerField::new('oldUserCount')
            ->setLabel('course.old_user')
            ->setColumns('col-sm-6 col-md-4')
            ->setRequired(false)
            ->hideOnIndex();
        //Index
        yield IdField::new('id')->onlyOnIndex();
        yield TextField::new('title')
            ->hideOnForm()
            ->setLabel('Заголовок');
        yield SlugField::new('slug')
            ->setTargetFieldName('slug')
            ->setLabel('Аліас URL')
            ->onlyOnIndex()
            ->setRequired(false);
        yield ImageField::new('imageName')
            ->setBasePath('upload/webinar')
            ->onlyOnIndex()
            ->setLabel('Зображення');
        yield IntegerField::new('viewsAmount')
            ->setLabel('Кількість переглядів')
            ->hideOnIndex()
            ->setColumns('col-sm-6 col-md-4');
        yield DateField::new('createdAt')
            ->onlyOnIndex()
            ->setLabel('Дата створення');
        yield BooleanField::new('isActive')
            ->setColumns('col-sm-6 col-md-4')
            ->hideOnForm()
            ->setLabel('Активна');
        yield BooleanField::new('registration_required')
            ->setLabel("Обов'язкова реєстрація")
            ->onlyOnIndex()
            ->setColumns('col-sm-6 col-md-4');
        yield TextField::new('commentsLink')
            ->setLabel('admin.comment.title')
            ->hideOnForm()
            ->setVirtual(true)
            ->formatValue(function ($value, Webinar $webinar) {
                $link = $this->urlGenerator
                    ->setController(CommentCrudController::class)
                    ->setAction(Action::INDEX)
                    ->set('query', $webinar->getId())
                    ->generateUrl();
                $linkTitle = '(' . $this->getDoctrine()->getRepository(Comment::class)->getCount(['item' => $webinar->getId()]) . ')';

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
            ->setEntityLabelInPlural('admin.dashboard.webinar.list_title')
            ->setEntityLabelInSingular('admin.dashboard.webinar.edit_button_title')
            ->setPageTitle(Crud::PAGE_NEW, 'admin.dashboard.webinar.new_page_title')
            ->setPageTitle(Crud::PAGE_EDIT, 'admin.dashboard.webinar.edit_page_title')
            ->setFormOptions(
                ['validation_groups' => ['Default', 'creation']],
                ['validation_groups' => ['Default', 'creation']]
            )
            ->setFormThemes([
                '@A2lixTranslationForm/bootstrap_4_layout.html.twig',
                '@FOSCKEditor/Form/ckeditor_widget.html.twig',
                '@EasyAdmin/crud/form_theme.html.twig',
            ]);
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->add(
                Crud::PAGE_INDEX,
                Action::new('webinarExport', 'Експорт')
                    ->createAsGlobalAction()
                    ->linkToCrudAction('webinarExport')
                    ->addCssClass('btn btn-primary')
            )
            ->add(
                Crud::PAGE_INDEX,
                Action::new('copy', false)
                    ->linkToCrudAction('copyWebinar')
                    ->setIcon('fas fa-copy')
                    ->setHtmlAttributes([
                        'placeholder' => 'Копіювати',
                    ])
            );
    }

    /**
     * @param ExportService $exportService
     *
     * @return void
     */
    public function webinarExport(ExportService $exportService): void
    {
        $query = $this->getDoctrine()
            ->getRepository('App:Webinar')
            ->createQueryBuilder('w')
            ->setMaxResults(1000)
            ->getQuery();

        $webinarList = $query->getResult();

        $exportData = [];

        /** @var Webinar $webinar */
        foreach ($webinarList as $webinar) {

            $expertList = [];
            foreach ($webinar->getExperts() as $expert) {
                $expertList[] = $expert->getName();
            }

            $partnerList = [];
            foreach ($webinar->getPartners() as $partner) {
                $partnerList[] = $partner->getName();
            }

            $tagList = [];
            foreach ($webinar->getTags() as $tag) {
                $tagList[] = $tag->getName();
            }

            $cropsList = [];
            foreach ($webinar->getCrops() as $crop) {
                $cropsList[] = $crop->getName();
            }

            $exportData[] = [
                'ID' => $webinar->getId(),
                'ID Конференції Zoom' => $webinar->getMeetingId(),
                'Стара кількість зареєстрованних користувачів' => $webinar->getOldUserCount(),
                'Назва' => $webinar->getTitle(),
                'Текст' => $webinar->getContent(),
                'Метатег title' => $webinar->getMetaTitle(),
                'Метатег кeywords' => $webinar->getKeywords(),
                'Метатег description' => $webinar->getDescription(),
                'Анонс' => $webinar->getShort(),
                'Партнери' => implode(', ', $partnerList),
                'Експерти' => implode(', ', $expertList),
                'Теги' => implode(', ', $tagList),
                'Культури' => implode(', ', $cropsList),
                'Категорія' => !is_null($webinar->getCategory()) ? $webinar->getCategory()->getName() : '',
                'Рейтинг' => $webinar->getRating(),
                'Кількість переглядів' => $webinar->getViewsAmount(),
                'Дата створення' => $webinar->getCreatedAt(),
                'Дата редагування' => $webinar->getUpdatedAt(),
            ];
        }

        $exportService->export('webinar', $exportData);
    }

    /**
     * Copy webinar.
     */
    public function copyWebinar(Request $request): Response
    {
        /** @var Webinar|null $webinar */
        $webinarId = (int)$request->query->get('entityId');
        $webinar = $this
            ->getDoctrine()
            ->getRepository(Webinar::class)
            ->findOneBy([
                'id' => $webinarId,
            ]);

        if (!$webinar) {
            throw new EntityNotFoundException([
                'entity_name' => Webinar::class,
                'entity_id_name' => 'id',
                'entity_id_value' => $webinarId,
            ]);
        }

        $webinarNew = $webinar->getCopy(
            $this->get('parameter_bag'),
            $this->fileManager
        );
        $this->persistEntity($this->entityManager, $webinarNew);

        $redirectUrl = $this->urlGenerator
            ->setController(self::class)
            ->setAction(Action::EDIT)
            ->setEntityId($webinarNew->getId())
            ->generateUrl();

        return $this->redirect($redirectUrl);
    }
}
