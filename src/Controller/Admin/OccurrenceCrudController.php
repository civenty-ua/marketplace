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
use App\Admin\Field\{
    EmptyField,
    TranslationField,
};
use App\Service\{
    ExportService,
    FileManager\FileManagerInterface,
};
use App\Form\Field\EmptyType;
use App\Entity\Occurrence;

class OccurrenceCrudController extends BaseCrudController
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
    )
    {
        $this->urlGenerator = $urlGenerator;
        $this->fileManager = $fileManager;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->currentLocale = $requestStack->getCurrentRequest()->getLocale();
    }

    public static function getEntityFqcn(): string
    {
        return Occurrence::class;
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
        ];

        yield TranslationField::new('translations', 'Переклади', $fieldsConfig)
            ->setRequired(true)
            ->hideOnIndex();

        $kernelDir = $this->get('parameter_bag')->get('kernel.project_dir');
        $imagesDir = $this->get('parameter_bag')->get('app.entity.files.occurrence');
        (new Filesystem())->mkdir($kernelDir . $imagesDir);

        //Form
        $img = ImageField::new('imageName')
            ->setUploadDir('public/upload/occurrence')
            ->setUploadedFileNamePattern('[timestamp]-[contenthash].[extension]')
            ->setBasePath('upload/occurrence')
            ->onlyOnForms()
            ->setColumns('col-sm-6 col-md-4 hide-delete-btn')
            ->setLabel('Зображення');
        if (Crud::PAGE_NEW === $pageName) {
            yield $img->setRequired(true);
        } else {
            yield $img->setRequired(false);
        }

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
        yield BooleanField::new('registration_required')
            ->setLabel("Обов'язкова реєстрація")
            ->onlyOnForms()
            ->setColumns('col-sm-6 col-md-4');
        yield NumberField::new('rating')
            ->setLabel('Старий Рейтинг')
            ->setRequired(false)
            ->setColumns('col-sm-6 col-md-4')
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
            ->setBasePath('upload/occurrence')
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
            ->formatValue(function ($value, Occurrence $occurrence) {
                $link = $this->urlGenerator
                    ->setController(CommentCrudController::class)
                    ->setAction(Action::INDEX)
                    ->set('query', $occurrence->getId())
                    ->generateUrl();
                $linkTitle = '(' . $this->getDoctrine()->getRepository(Comment::class)->getCount(['item' => $occurrence->getId()]) . ')';

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
            ->setEntityLabelInPlural('admin.dashboard.occurrence.list_title')
            ->setEntityLabelInSingular('admin.dashboard.occurrence.edit_button_title')
            ->setPageTitle(Crud::PAGE_NEW, 'admin.dashboard.occurrence.new_page_title')
            ->setPageTitle(Crud::PAGE_EDIT, 'admin.dashboard.occurrence.edit_page_title')
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
                    ->linkToCrudAction('copyOccurrence')
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
            ->getRepository('App:Occurrence')
            ->createQueryBuilder('w')
            ->setMaxResults(1000)
            ->getQuery();

        $occurrenceList = $query->getResult();

        $exportData = [];

        /** @var Occurrence $occurrence */
        foreach ($occurrenceList as $occurrence) {

            $expertList = [];
            foreach ($occurrence->getExperts() as $expert) {
                $expertList[] = $expert->getName();
            }

            $partnerList = [];
            foreach ($occurrence->getPartners() as $partner) {
                $partnerList[] = $partner->getName();
            }

            $tagList = [];
            foreach ($occurrence->getTags() as $tag) {
                $tagList[] = $tag->getName();
            }

            $cropsList = [];
            foreach ($occurrence->getCrops() as $crop) {
                $cropsList[] = $crop->getName();
            }

            $exportData[] = [
                'ID' => $occurrence->getId(),
                'Стара кількість зареєстрованних користувачів' => $occurrence->getOldUserCount(),
                'Назва' => $occurrence->getTitle(),
                'Текст' => $occurrence->getContent(),
                'Метатег title' => $occurrence->getMetaTitle(),
                'Метатег кeywords' => $occurrence->getKeywords(),
                'Метатег description' => $occurrence->getDescription(),
                'Анонс' => $occurrence->getShort(),
                'Партнери' => implode(', ', $partnerList),
                'Експерти' => implode(', ', $expertList),
                'Теги' => implode(', ', $tagList),
                'Культури' => implode(', ', $cropsList),
                'Категорія' => !is_null($occurrence->getCategory()) ? $occurrence->getCategory()->getName() : '',
                'Рейтинг' => $occurrence->getRating(),
                'Кількість переглядів' => $occurrence->getViewsAmount(),
                'Дата створення' => $occurrence->getCreatedAt(),
                'Дата редагування' => $occurrence->getUpdatedAt(),
            ];
        }

        $exportService->export('webinar', $exportData);
    }

    /**
     * Copy webinar.
     */
    public function copyOccurrence(Request $request): Response
    {
        /** @var Occurrence|null $occurrence */
        $occurrenceId = (int)$request->query->get('entityId');
        $occurrence = $this
            ->getDoctrine()
            ->getRepository(Occurrence::class)
            ->findOneBy([
                'id' => $occurrenceId,
            ]);

        if (!$occurrence) {
            throw new EntityNotFoundException([
                'entity_name' => Occurrence::class,
                'entity_id_name' => 'id',
                'entity_id_value' => $occurrenceId,
            ]);
        }

        $occurrenceNew = $occurrence->getCopy(
            $this->get('parameter_bag'),
            $this->fileManager
        );
        $this->persistEntity($this->entityManager, $occurrenceNew);

        $redirectUrl = $this->urlGenerator
            ->setController(self::class)
            ->setAction(Action::EDIT)
            ->setEntityId($occurrenceNew->getId())
            ->generateUrl();

        return $this->redirect($redirectUrl);
    }
}
