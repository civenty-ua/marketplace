<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use App\Entity\News;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\FileUploadType;
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
use App\Entity\Article;

class NewsCrudController extends BaseCrudController
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
        return News::class;
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
        ];

        $kernelDir = $this->get('parameter_bag')->get('kernel.project_dir');
        $imagesDir = $this->get('parameter_bag')->get('app.entity.files.article');
        (new Filesystem())->mkdir($kernelDir . $imagesDir);

        yield TranslationField::new('translations', 'Переклади', $fieldsConfig)
            ->setRequired(true)
            ->hideOnIndex()
            ->addCssClass('col-md-12 float');

        $img = ImageField::new('imageName')
            ->setUploadDir('public/upload/article')
            ->setUploadedFileNamePattern('[timestamp]-[contenthash].[extension]')
            ->setBasePath('upload/article')
            ->setLabel('Зображення')
            ->setColumns('col-sm-6 col-md-4 hide-delete-btn')
            ->onlyOnForms();

        if (Crud::PAGE_NEW === $pageName) {
            yield $img->setRequired(true);
        } else {
            yield $img->setRequired(false);
        }
        yield SlugField::new('slug')
            ->setTargetFieldName('slug')
            ->setLabel('Аліас URL')
            ->setRequired(false)
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms();
        yield BooleanField::new('top')
            ->setLabel('Показувати на головній сторінці')
            ->onlyOnForms()
            ->setColumns('col-sm-6 col-md-4');

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
            ->onlyOnForms();

        yield AssociationField::new('feedbackForm')
            ->setLabel('Форма зворотного зв’язку')
            ->onlyOnForms()
            ->setColumns('col-sm-6 col-md-4');

        yield BooleanField::new('isActive')
            ->setLabel('Активна')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms();

        yield AssociationField::new('region')
            ->setLabel('Область')
            ->onlyOnForms()
            ->setColumns('col-sm-6 col-md-4')
            ->setFormTypeOptions([
                'query_builder' => function(EntityRepository $repository) {
                    return $repository
                        ->createQueryBuilder('region')
                        ->leftJoin('region.translations', 'regionTranslations')
                        ->where('regionTranslations.locale = :locale')
                        ->setParameter('locale',  $this->currentLocale)
                        ->addOrderBy('region.sort', 'ASC')
                        ->addOrderBy('regionTranslations.name', 'ASC');
                },
            ]);

        yield AssociationField::new('crops')
            ->onlyOnForms()->setLabel('Культури')
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

        yield BooleanField::new('commentsAllowed')
            ->setLabel('Дозволені коментарі')
            ->onlyOnForms()
            ->setColumns('col-sm-6 col-md-4');

        yield IntegerField::new('viewsAmount')
            ->setLabel('Кількість переглядів')
            ->onlyOnForms()
            ->setColumns('col-sm-6 col-md-4');

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

        yield BooleanField::new('registration_required')
            ->setLabel("Обов'язкова реєстрація")
            ->onlyOnForms()
            ->setColumns('col-sm-6 col-md-4');

        yield AssociationField::new('tags')
            ->onlyOnForms()->setLabel('Теги')
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
        yield AssociationField::new('similar')
            ->onlyOnForms()->setLabel('Схожі матеріали')
            ->setColumns('col-sm-6 col-md-4')
            ->setFormTypeOptions([
                'by_reference' => true,
                'query_builder' => $this->getDoctrine()->getRepository(News::class)
                    ->createQueryBuilder('a')->orderBy('a.createdAt', 'DESC')
            ]);

        yield IdField::new('id')
            ->onlyOnIndex();
        yield TextField::new('title')
            ->hideOnForm()
            ->setLabel('Назва');
        yield SlugField::new('slug')
            ->setTargetFieldName('slug')
            ->setLabel('Аліас URL')
            ->setRequired(false)
            ->onlyOnIndex();

        yield BooleanField::new('isActive')
            ->setLabel('Активна')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnIndex();


        yield AssociationField::new('category')
            ->setLabel('Категорія')
            ->onlyOnIndex();

        yield DateField::new('createdAt')
            ->setColumns('col-sm-6 col-md-4')
            ->hideWhenCreating()
            ->setLabel('Дата створення');

        yield ImageField::new('imageName')
            ->setBasePath('upload/article')
            ->setLabel('Зображення')
            ->onlyOnIndex();
        yield TextField::new('commentsLink')
            ->setLabel('admin.comment.title')
            ->hideOnForm()
            ->setVirtual(true)
            ->formatValue(function ($value, News $news) {
                $link = $this->urlGenerator
                    ->setController(CommentCrudController::class)
                    ->setAction(Action::INDEX)
                    ->set('query', $news->getId())
                    ->generateUrl();
                $linkTitle = '(' . $this->getDoctrine()->getRepository(Comment::class)->getCount(['item' => $news->getId()]) . ')';

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
            ->setFormOptions(
                ['validation_groups' => ['Default', 'creation']],
                ['validation_groups' => ['Default', 'creation']]
            )
            ->setEntityLabelInPlural('admin.dashboard.news.list_title')
            ->setEntityLabelInSingular('admin.dashboard.news.edit_button_title')
            ->setPageTitle(Crud::PAGE_NEW, 'admin.dashboard.news.new_page_title')
            ->setPageTitle(Crud::PAGE_EDIT, 'admin.dashboard.news.edit_page_title')
            ->addFormTheme('admin/article/article_theme.html.twig')
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
                Action::new('articleExport', 'Експорт')
                    ->createAsGlobalAction()
                    ->linkToCrudAction('articleExport')
                    ->addCssClass('btn btn-primary')
            )
            ->add(
                Crud::PAGE_INDEX,
                Action::new('copy', false)
                    ->linkToCrudAction('copyArticle')
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
    public function articleExport(ExportService $exportService): void
    {
        $query = $this->getDoctrine()
            ->getRepository('App:News')
            ->createQueryBuilder('a')
            ->setMaxResults(1000)
            ->getQuery();

        $newsList = $query->getResult();

        $exportData = [];

        /** @var News $news */
        foreach ($newsList as $news) {

            $expertList = [];
            foreach ($news->getExperts() as $expert) {
                $expertList[] = $expert->getName();
            }

            $partnerList = [];
            foreach ($news->getPartners() as $partner) {
                $partnerList[] = $partner->getName();
            }

            $tagList = [];
            foreach ($news->getTags() as $tag) {
                $tagList[] = $tag->getName();
            }

            $cropsList = [];
            foreach ($news->getCrops() as $crop) {
                $cropsList[] = $crop->getName();
            }

            $exportData[] = [
                'ID' => $news->getId(),
                'Назва' => $news->getTitle(),
                'Текст' => $news->getContent(),
                'Метатег title' => $news->getMetaTitle(),
                'Метатег кeywords' => $news->getKeywords(),
                'Метатег description' => $news->getDescription(),
                'Анонс' => $news->getShort(),
                'Партнери' => implode(', ', $partnerList),
                'Експерти' => implode(', ', $expertList),
                'Теги' => implode(', ', $tagList),
                'Культури' => implode(', ', $cropsList),
                'Категорія' => !is_null($news->getCategory()) ? $news->getCategory()->getName() : '',
                'Рейтинг' => $news->getRating(),
                'Кількість переглядів' => $news->getViewsAmount(),
                'Дата створення' => $news->getCreatedAt(),
                'Дата редагування' => $news->getUpdatedAt(),
            ];
        }

        $exportService->export('article', $exportData);
    }

    /**
     * Copy article.
     */
    public function copyArticle(Request $request): Response
    {
        /** @var News|null $news */
        $newsId = (int)$request->query->get('entityId');
        $news = $this
            ->getDoctrine()
            ->getRepository(News::class)
            ->findOneBy([
                'id' => $newsId,
            ]);

        if (!$news) {
            throw new EntityNotFoundException([
                'entity_name' => Article::class,
                'entity_id_name' => 'id',
                'entity_id_value' => $newsId,
            ]);
        }

        $newsNew = $news->getCopy(
            $this->get('parameter_bag'),
            $this->fileManager
        );
        $this->persistEntity($this->entityManager, $newsNew);

        $redirectUrl = $this->urlGenerator
            ->setController(self::class)
            ->setAction(Action::EDIT)
            ->setEntityId($newsNew->getId())
            ->generateUrl();

        return $this->redirect($redirectUrl);
    }
}
