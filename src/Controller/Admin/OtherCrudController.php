<?php

namespace App\Controller\Admin;

use App\Admin\Field\TranslationField;
use App\Entity\Other;
use App\Service\ExportService;
use Doctrine\ORM\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class OtherCrudController extends BaseCrudController
{
    private ?string $currentLocale;

    public function __construct(RequestStack  $requestStack)
    {
        $this->currentLocale = $requestStack->getCurrentRequest()->getLocale();
    }

    public static function getEntityFqcn(): string
    {
        return Other::class;
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

        yield AssociationField::new('videoItem')
            ->onlyOnForms()
            ->setColumns('col-sm-6 col-md-4')

            ->setLabel('Відео');
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
        yield BooleanField::new('isActive')
            ->setLabel('Активна')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms();

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
            ->onlyOnForms()->setLabel('Культури')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms()
            ->setFormTypeOptions([
                'by_reference' => false,
                'query_builder' => function(EntityRepository $repository) {
                    return $repository
                        ->createQueryBuilder('crops')
                        ->leftJoin('crops.translations', 'cropsTranslations')
                        ->where('cropsTranslations.locale = :locale')
                        ->setParameter('locale',  $this->currentLocale)
                        ->orderBy('cropsTranslations.name', 'ASC');
                },
            ]);
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
        yield SlugField::new('slug')
            ->setTargetFieldName('slug')
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('Аліас URL')

            ->onlyOnForms()
            ->setRequired(false);

        yield BooleanField::new('top')
            ->setLabel('Показувати на головній сторінці')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms();

        yield IntegerField::new('viewsAmount')
            ->setLabel('Кількість переглядів')
            ->hideOnIndex()
            ->setColumns('col-sm-6 col-md-4');

        yield TranslationField::new('translations', 'Переклади', $fieldsConfig)
            ->setRequired(true)
            ->hideOnIndex();


        yield IdField::new('id')
            ->onlyOnIndex();
        yield TextField::new('title')
            ->hideOnForm()
            ->setLabel('Заголовок');
        yield SlugField::new('slug')
            ->setTargetFieldName('slug')
            ->setLabel('Аліас URL')
            ->onlyOnIndex()
            ->setRequired(false);
        yield AssociationField::new('category')
            ->setLabel('Категорія')
            ->onlyOnIndex();
        yield TextField::new('videoItem')
            ->onlyOnIndex()
            ->setTemplatePath('admin/video/other_youtube.html.twig')
            ->setLabel('Відео')
            ->setFormTypeOptions([
                'block_name' => 'youtube_link',
            ]);
        yield BooleanField::new('isActive')
            ->setLabel('Активна')
            ->onlyOnIndex();
        yield DateField::new('createdAt')
            ->onlyOnIndex()
            ->setLabel('Дата створення');
        yield IntegerField::new('viewsAmount')
            ->setLabel('Кількість переглядів')
            ->onlyOnIndex();

    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('videoItem')
            ->add('category')
            ->add('tags')
            ->add('createdAt')
            ->add('updatedAt');
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['translations.title'])
            ->setEntityLabelInPlural('admin.dashboard.other.list_title')
            ->setEntityLabelInSingular('admin.dashboard.other.edit_button_title')
            ->setPageTitle(Crud::PAGE_NEW, 'admin.dashboard.other.new_page_title')
            ->setPageTitle(Crud::PAGE_EDIT, 'admin.dashboard.other.edit_page_title')
            ->setFormOptions(['validation_groups' => ['Default', 'creation']],
                ['validation_groups' => ['Default', 'creation']])
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
            ->add(Crud::PAGE_INDEX,
                Action::new('otherExport', 'Export')
                    ->createAsGlobalAction()
                    ->linkToCrudAction('otherExport')
                    ->addCssClass('btn btn-primary'));
    }

    /**
     * @param ExportService $exportService
     *
     * @return void
     */
    public function otherExport(ExportService $exportService): void
    {
        $query = $this->getDoctrine()
            ->getRepository('App:Other')
            ->createQueryBuilder('o')
            ->setMaxResults(1000)
            ->getQuery();

        $otherList = $query->getResult();

        $exportData = [];

        /** @var Other $other */
        foreach ($otherList as $other) {

            $tagList = [];
            foreach ($other->getTags() as $tag) {
                $tagList[] = $tag->getName();
            }

            $exportData[] = [
                'ID' => $other->getId(),
                'Назва' => $other->getTitle(),
                'Текст' => $other->getContent(),
                'Метатег title' => $other->getMetaTitle(),
                'Метатег кeywords' => $other->getKeywords(),
                'Метатег description' => $other->getDescription(),
                'Анонс' => $other->getShort(),
                'Теги' => implode(', ', $tagList),
                'Категорія' => $other->getCategory()->getName(),
                'Рейтинг' => $other->getRating(),
                'Кількість переглядів' => $other->getViewsAmount(),
                'Дата створення' => $other->getCreatedAt(),
                'Дата редагування' => $other->getUpdatedAt(),
            ];
        }

        $exportService->export('other', $exportData);
    }
}
