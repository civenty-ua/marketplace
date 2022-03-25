<?php

namespace App\Controller\Admin;

use App\Admin\Field\TranslationField;
use App\Entity\Expert;
use App\Form\Field\EmptyType;
use App\Service\ExportService;
use Doctrine\ORM\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class ExpertCrudController extends BaseCrudController
{
    private ?string $currentLocale;

    public function __construct(RequestStack $requestStack)
    {
        $this->currentLocale = $requestStack->getCurrentRequest()->getLocale();
    }

    public static function getEntityFqcn(): string
    {
        return Expert::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $kernelDir = $this->get('parameter_bag')->get('kernel.project_dir');
        (new Filesystem())->mkdir("$kernelDir/public/upload/expert");

        yield TranslationField::new('translations', 'Переклад', [
                'name' => [
                    'field_type' => TextType::class,
                    'required' => true,
                    'label' => 'Назва',
                    'row_attr' => [
                        'class' => 'col-md-4 ',
                    ],
                ],
                'content' => [
                    'field_type' => CKEditorType::class,
                    'required' => false,
                    'label' => 'Опис',
                    'attr' => [
                        'data-max-length' => 50,
                    ],
                    'row_attr' => [
                        'class' => 'col-md-12 ',
                    ],
                ],
                'position' => [
                    'field_type' => CKEditorType::class,
                    'required' => false,
                    'label' => 'Позиція',
                    'attr' => [
                        'data-max-length' => 50,
                    ],
                    'row_attr' => [
                        'class' => 'col-md-12 ',
                    ],
                ],
                'address' => [
                    'field_type' => TextType::class,
                    'required' => false,
                    'label' => 'Адреса',
                    'row_attr' => [
                        'class' => 'col-md-4 ',
                    ],
                ],
                'description' => [
                    'field_type' => TextareaType::class,
                    'required' => false,
                    'label' => 'Метатег description',
                    'row_attr' => [
                        'class' => 'col-md-4 ',
                    ],
                ],
                'keywords' => [
                    'field_type' => TextType::class,
                    'required' => false,
                    'label' => 'Метатег keywords',
                    'row_attr' => [
                        'class' => 'col-md-4 ',
                    ],
                ],
                '_empty' => [
                    'field_type' => EmptyType::class,
                    'required' => false,
                    'mapped' => false,
                    'label' => ' ',
                    'row_attr' => [
                        'class' => 'clearfix',
                    ],
                ],

            ])
            ->setRequired(true)
            ->hideOnIndex();
        yield ImageField::new('image')
            ->setUploadDir('public/upload/expert')
            ->setUploadedFileNamePattern('[timestamp]-[contenthash].[extension]')
            ->setBasePath('upload/expert')
            ->onlyOnForms()
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('Зображення')
            ->setRequired(Crud::PAGE_NEW === $pageName);
        yield SlugField::new('slug')
            ->setTargetFieldName('slug')
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('Аліас URL')
            ->onlyOnForms()
            ->setRequired(false);
        yield AssociationField::new('expertTypes')
            ->onlyOnForms()
            ->setLabel('Позиція')
            ->setColumns('col-sm-6 col-md-4')
            ->setFormTypeOptions([
                'by_reference' => false,
                'query_builder' => function(EntityRepository $repository) {
                    return $repository
                        ->createQueryBuilder('expertTypes')
                        ->leftJoin('expertTypes.translations', 'expertTypesTranslations')
                        ->where('expertTypesTranslations.locale = :locale')
                        ->setParameter('locale',  $this->currentLocale)
                        ->orderBy('expertTypesTranslations.name', 'ASC');
                },
            ]);
        yield AssociationField::new('tags')
            ->setRequired(true)
            ->onlyOnForms()
            ->setLabel('Теги')
            ->setColumns('col-sm-6 col-md-4')
            ->setFormTypeOptions([
                'required' => true,
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
        yield TextField::new('phone')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms()
            ->setLabel('Телефон');
        yield TextField::new('site')
            ->onlyOnForms()
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('Адреса сайта');
        yield TextField::new('email')
            ->setLabel('Email')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms();
        yield TextField::new('youtube')
            ->setLabel('Youtube')
            ->onlyOnForms()
            ->setColumns('col-sm-6 col-md-4');
        yield TextField::new('linkedin')
            ->setLabel('LinkedIn')
            ->onlyOnForms()
            ->setColumns('col-sm-6 col-md-4');
        yield TextField::new('facebook')
            ->setLabel('Facebook')
            ->onlyOnForms()
            ->setColumns('col-sm-6 col-md-4');
        yield TextField::new('instagram')
            ->setLabel('Instagram')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms();
        yield TextField::new('telegram')
            ->setLabel('Telegram')
            ->onlyOnForms()
            ->setColumns('col-sm-6 col-md-4');

        //Index
        yield IdField::new('id')
            ->hideOnForm();
        yield TextField::new('name')
            ->hideOnForm()
            ->setLabel('Назва');
        yield TextField::new('phone')
            ->onlyOnIndex()
            ->setLabel('Телефон');
        yield TextField::new('email')
            ->setLabel('Email')
            ->onlyOnIndex();
        yield ImageField::new('image')
            ->setBasePath('upload/expert')
            ->onlyOnIndex()
            ->setLabel('Зображення');
        yield DateField::new('createdAt')
            ->hideOnForm()
            ->setLabel('Дата створення');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('createdAt')
            ->add('updatedAt');
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setEntityLabelInPlural('admin.dashboard.expert.list_title')
            ->setEntityLabelInSingular('admin.dashboard.expert.edit_button_title')
            ->setPageTitle(Crud::PAGE_NEW, 'admin.dashboard.expert.new_page_title')
            ->setPageTitle(Crud::PAGE_EDIT, 'admin.dashboard.expert.edit_page_title')
            ->setSearchFields(['translations.name'])
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
                Action::new('expertExport', 'Export')
                    ->createAsGlobalAction()
                    ->linkToCrudAction('expertExport')
                    ->addCssClass('btn btn-primary'));
    }

    /**
     * @param ExportService $exportService
     *
     * @return void
     */
    public function expertExport(ExportService $exportService): void
    {
        $query = $this->getDoctrine()
            ->getRepository('App:Expert')
            ->createQueryBuilder('e')
            ->setMaxResults(1000)
            ->getQuery();

        $expertList = $query->getResult();

        $exportData = [];

        /** @var Expert $expert */
        foreach ($expertList as $expert) {

            $exportData[] = [
                'ID' => $expert->getId(),
                'Назва' => $expert->getName(),
                'Теги' => implode(', ', $expert->getTags()->getValues()),
                'Позиція' => implode(', ',$expert->getExpertTypes()->getValues()),
                'Телефон' => $expert->getPhone(),
                'Eлектронна пошта' => $expert->getEmail(),
                'Сайт' => $expert->getSite(),
                'Facebook' => $expert->getFacebook(),
                'Linkedin' => $expert->getLinkedin(),
                'Youtube' => $expert->getYoutube(),
                'Telegram' => $expert->getTelegram(),
                'Instagram' => $expert->getInstagram(),
                'Адреса' => $expert->getAddress(),
                'Текст' => $expert->getContent(),
                'Метатег title' => $expert->getMetaTitle(),
                'Метатег кeywords' => $expert->getKeywords(),
                'Метатег description' => $expert->getDescription(),
                'Дата створення' => $expert->getCreatedAt(),
                'Дата редагування' => $expert->getUpdatedAt(),
            ];
        }

        $exportService->export('expert', $exportData);
    }
}
