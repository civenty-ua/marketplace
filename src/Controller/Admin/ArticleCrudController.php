<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use App\Repository\CategoryRepository;
use App\Repository\TypePageRepository;
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

class ArticleCrudController extends BaseCrudController
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
        return Article::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $fieldsConfig = [
            'title' => [
                'field_type' => TextType::class,
                'required' => true,
                'label' => '??????????????????',
                'row_attr' => [
                    'class' => 'col-md-4',
                ],
                'block_name' => 'title',
                'block_prefix' => 'translate',
            ],
            'metaTitle' => [
                'field_type' => TextType::class,
                'required' => false,
                'label' => '?????????????? title',
                'row_attr' => [
                    'class' => 'col-md-4',
                ],
            ],
            'keywords' => [
                'field_type' => TextType::class,
                'required' => false,
                'label' => '?????????????? ??eywords',
                'row_attr' => [
                    'class' => 'col-md-4',
                ],
            ],
            'description' => [
                'field_type' => TextareaType::class,
                'required' => false,
                'label' => '?????????????? description',
                'row_attr' => [
                    'class' => 'col-md-4 article-description',
                ],
                'attr' => [
                    'class' => 'article-description',
                ],
            ],
            'short' => [
                'field_type' => TextareaType::class,
                'required' => false,
                'label' => '??????????',
                'row_attr' => [
                    'class' => 'col-md-8 float-right padding-left-short',
                ],
                'attr' => [
                    'class' => 'webinar-short'
                ]
            ],
            'content' => [
                'field_type' => CKEditorType::class,
                'required' => false,
                'label' => '??????????',
                'row_attr' => [
                    'class' => 'col-md-12 float',
                ],
            ],
        ];

        $kernelDir = $this->get('parameter_bag')->get('kernel.project_dir');
        $imagesDir = $this->get('parameter_bag')->get('app.entity.files.article');
        (new Filesystem())->mkdir($kernelDir . $imagesDir);

        yield TranslationField::new('translations', '??????????????????', $fieldsConfig)
            ->setRequired(true)
            ->hideOnIndex()
            ->addCssClass('col-md-12');

        yield AssociationField::new('typePage')
            ->setLabel('?????? ????????????????')
            ->setColumns('col-sm-6 col-md-4')
            ->setRequired(true)
            ->setFormTypeOption('attr', ['required' => 'required'])
            ->setFormTypeOption('query_builder',function (TypePageRepository $typePage){
                return $typePage->createQueryBuilder('tp')
                    ->andWhere('tp.code NOT IN (:list)')
                    ->setParameter('list', ['business_tools','news']);
            })
            ->onlyOnForms();
        yield SlugField::new('slug')
            ->setTargetFieldName('slug')
            ->setLabel('?????????? URL')
            ->setRequired(false)
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms();
        yield BooleanField::new('top')
            ->setLabel('???????????????????? ???? ???????????????? ????????????????')
            ->onlyOnForms()
            ->setColumns('col-sm-6 col-md-4');
        yield ImageField::new('imageName')
            ->setUploadDir('public/upload/article')
            ->setUploadedFileNamePattern('[timestamp]-[contenthash].[extension]')
            ->setBasePath('upload/article')
            ->setLabel('????????????????????')
            ->setRequired(Crud::PAGE_NEW === $pageName)
            ->setColumns('col-sm-6 col-md-4 hide-delete-btn')
            ->onlyOnForms();
        yield AssociationField::new('category')
            ->setLabel('??????????????????')
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
        yield BooleanField::new('isActive')
            ->setLabel('??????????????')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms();
        yield AssociationField::new('feedbackForm')
            ->setLabel('?????????? ???????????????????? ???????????????')
            ->onlyOnForms()
            ->setColumns('col-sm-6 col-md-4');
        yield AssociationField::new('region')
            ->setLabel('??????????????')
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
        yield BooleanField::new('commentsAllowed')
            ->setLabel('?????????????????? ??????????????????')
            ->onlyOnForms()
            ->setColumns('col-sm-6 col-md-4');
        yield AssociationField::new('crops')
            ->onlyOnForms()->setLabel('????????????????')
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
        yield IntegerField::new('viewsAmount')
            ->setLabel('?????????????????? ????????????????????')
            ->onlyOnForms()
            ->setColumns('col-sm-6 col-md-4');
        yield BooleanField::new('registration_required')
            ->setLabel("????????'???????????? ????????????????????")
            ->onlyOnForms()
            ->setColumns('col-sm-6 col-md-4');
        yield AssociationField::new('experts')
            ->onlyOnForms()->setLabel('????????????????')
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
        yield AssociationField::new('tags')
            ->onlyOnForms()->setLabel('????????')
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
        yield EmptyField::new();
        yield AssociationField::new('partners')
            ->onlyOnForms()
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('????????????????')
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
            ->onlyOnForms()->setLabel('?????????? ??????????????????')
            ->setColumns('col-sm-6 col-md-4')
            ->setFormTypeOptions([
                'by_reference' => true,
                'query_builder' => $this->getDoctrine()->getRepository(Article::class)
                    ->createQueryBuilder('a')->orderBy('a.createdAt','DESC')
            ]);
        yield IdField::new('id')
            ->onlyOnIndex();
        yield TextField::new('title')
            ->hideOnForm()
            ->setLabel('??????????');
        yield SlugField::new('slug')
            ->setTargetFieldName('slug')
            ->setLabel('?????????? URL')
            ->setRequired(false)
            ->onlyOnIndex();
        yield BooleanField::new('isActive')
            ->setLabel('??????????????')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnIndex();
        yield AssociationField::new('category')
            ->setLabel('??????????????????')
            ->onlyOnIndex();
        yield AssociationField::new('typePage')
            ->setLabel('?????? ????????????????')
            ->onlyOnIndex();
        yield DateField::new('createdAt')
            ->setColumns('col-sm-6 col-md-4')
            ->hideWhenCreating()
            ->setLabel('???????? ??????????????????');

        yield ImageField::new('imageName')
            ->setBasePath('upload/article')
            ->setLabel('????????????????????')
            ->onlyOnIndex();
        yield TextField::new('commentsLink')
            ->setLabel('admin.comment.title')
            ->hideOnForm()
            ->setVirtual(true)
            ->formatValue(function ($value, Article $article) {
                $link = $this->urlGenerator
                    ->setController(CommentCrudController::class)
                    ->setAction(Action::INDEX)
                    ->set('query', $article->getId())
                    ->generateUrl();
                $linkTitle = '(' . $this->getDoctrine()->getRepository(Comment::class)->getCount(['item' => $article->getId()]) . ')';

                return "<a target=\"_blank\" href=\"$link\">$linkTitle</a>";
            });
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('isActive')
            ->add('typePage')
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
            ->setEntityLabelInPlural('admin.dashboard.article.list_title')
            ->setEntityLabelInSingular('admin.dashboard.article.edit_button_title')
            ->setPageTitle(Crud::PAGE_NEW, 'admin.dashboard.article.new_page_title')
            ->setPageTitle(Crud::PAGE_EDIT, 'admin.dashboard.article.edit_page_title')
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
                Action::new('articleExport', '??????????????')
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
                        'placeholder' => '??????????????????',
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
            ->getRepository('App:Article')
            ->createQueryBuilder('a')
            ->setMaxResults(1000)
            ->getQuery();

        $articleList = $query->getResult();

        $exportData = [];

        /** @var Article $article */
        foreach ($articleList as $article) {

            $expertList = [];
            foreach ($article->getExperts() as $expert) {
                $expertList[] = $expert->getName();
            }

            $partnerList = [];
            foreach ($article->getPartners() as $partner) {
                $partnerList[] = $partner->getName();
            }

            $tagList = [];
            foreach ($article->getTags() as $tag) {
                $tagList[] = $tag->getName();
            }

            $cropsList = [];
            foreach ($article->getCrops() as $crop) {
                $cropsList[] = $crop->getName();
            }

            $exportData[] = [
                'ID' => $article->getId(),
                '??????????' => $article->getTitle(),
                '??????????' => $article->getContent(),
                '?????????????? title' => $article->getMetaTitle(),
                '?????????????? ??eywords' => $article->getKeywords(),
                '?????????????? description' => $article->getDescription(),
                '??????????' => $article->getShort(),
                '????????????????' => implode(', ', $partnerList),
                '????????????????' => implode(', ', $expertList),
                '????????' => implode(', ', $tagList),
                '????????????????' => implode(', ', $cropsList),
                '?????? ????????????????' => $article->getTypePage()->getName(),
                '??????????????????' => !is_null($article->getCategory()) ? $article->getCategory()->getName() : '',
                '??????????????' => $article->getRating(),
                '?????????????????? ????????????????????' => $article->getViewsAmount(),
                '???????? ??????????????????' => $article->getCreatedAt(),
                '???????? ??????????????????????' => $article->getUpdatedAt(),
            ];
        }

        $exportService->export('article', $exportData);
    }

    /**
     * Copy article.
     */
    public function copyArticle(Request $request): Response
    {
        /** @var Article|null $article */
        $articleId = (int)$request->query->get('entityId');
        $article = $this
            ->getDoctrine()
            ->getRepository(Article::class)
            ->findOneBy([
                'id' => $articleId,
            ]);

        if (!$article) {
            throw new EntityNotFoundException([
                'entity_name' => Article::class,
                'entity_id_name' => 'id',
                'entity_id_value' => $articleId,
            ]);
        }

        $articleNew = $article->getCopy(
            $this->get('parameter_bag'),
            $this->fileManager
        );
        $this->persistEntity($this->entityManager, $articleNew);

        $redirectUrl = $this->urlGenerator
            ->setController(self::class)
            ->setAction(Action::EDIT)
            ->setEntityId($articleNew->getId())
            ->generateUrl();

        return $this->redirect($redirectUrl);
    }
}
