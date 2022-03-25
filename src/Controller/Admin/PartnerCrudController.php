<?php

namespace App\Controller\Admin;

use App\Admin\Field\EmptyField;
use App\Admin\Field\TranslationField;
use App\Entity\Partner;
use App\Service\ExportService;
use Doctrine\ORM\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
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

class PartnerCrudController extends BaseCrudController
{
    private ?string $currentLocale;

    public function __construct(RequestStack $requestStack)
    {
        $this->currentLocale = $requestStack->getCurrentRequest()->getLocale();
    }

    public static function getEntityFqcn(): string
    {
        return Partner::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $fieldsConfig = [
            'name' => [
                'field_type' => TextType::class,
                'required' => true,
                'label' => 'Назва',
                'row_attr' => [
                    'class' => 'col-md-4 float',
                ],
            ],
            'content' => [
                'field_type' => CKEditorType::class,
                'required' => false,
                'label' => 'Опис',
            ],
            'address' => [
                'field_type' => TextType::class,
                'required' => false,
                'label' => 'Адреса',
                'row_attr' => [
                    'class' => 'col-md-4',
                ],
            ],
            'bottomContent' => [
                'field_type' => CKEditorType::class,
                'required' => false,
                'label' => 'Контент блоку ресурси партнера',
            ],
            'description' => [
                'field_type' => TextareaType::class,
                'required' => false,
                'label' => 'Метатег description',
                'row_attr' => [
                    'class' => 'col-md-4',
                ],
            ],
            'keywords' => [
                'field_type' => TextType::class,
                'required' => false,
                'label' => 'Метатег keywords',
                'row_attr' => [
                    'class' => 'col-md-4',
                ],
            ],

        ];

        $kernelDir = $this->get('parameter_bag')->get('kernel.project_dir');
        $dir = $kernelDir . '/public/upload/partner';
        $fs = new Filesystem();
        $fs->mkdir($dir);

        yield SlugField::new('slug')
            ->setTargetFieldName('slug')
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('Аліас URL')
            ->onlyOnForms()
            ->setRequired(false);

        yield TranslationField::new('translations', 'Переклад', $fieldsConfig)
            ->setRequired(true)
            ->hideOnIndex();
        $img = ImageField::new('image')
            ->setUploadDir('public/upload/partner')
            ->setUploadedFileNamePattern('[timestamp]-[contenthash].[extension]')
            ->setBasePath('upload/partner')
            ->setColumns('col-sm-6 col-md-4 hide-delete-btn')
            ->setLabel('Зображення')
            ->onlyOnForms();
        if (Crud::PAGE_NEW === $pageName) {
            yield $img->setRequired(true);
        } else {
            yield $img->setRequired(false);
        }

        yield AssociationField::new('region')
            ->setLabel('Область')
            ->onlyOnForms()
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
            ])
            ->setColumns('col-sm-6 col-md-4');
        yield TextField::new('phone')
            ->setLabel('Телефон')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms();
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
        yield TextField::new('twitter')
            ->setLabel('Twitter')
            ->onlyOnForms()
            ->setColumns('col-sm-6 col-md-4');
        yield TextField::new('facebook')
            ->setLabel('Facebook')
            ->onlyOnForms()
            ->setColumns('col-sm-6 col-md-4');
        yield TextField::new('instagram')
            ->setLabel('Instagram')
            ->onlyOnForms()
            ->setColumns('col-sm-6 col-md-4');
        yield TextField::new('telegram')
            ->setLabel('Telegram')
            ->onlyOnForms()
            ->setColumns('col-sm-6 col-md-4');
        yield TextField::new('zoomClientSecret')
            ->hideOnIndex()
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('Zoom Client Secret партнера');
        yield TextField::new('zoomApiKey')
            ->hideOnIndex()
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('Zoom Api Ключ партнера');



        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name')->hideOnForm()->setLabel('Назва');
        yield TextField::new('email')
            ->setLabel('Email')
            ->onlyOnIndex();
        yield ImageField::new('image')
            ->hideOnForm()
            ->setBasePath('upload/partner')
            ->setLabel('Зображення');
        yield AssociationField::new('region')->setLabel('Область')->hideOnForm();
        yield DateField::new('createdAt')->hideOnForm()->setLabel('Дата створення');
        yield BooleanField::new('isShownOnFront')->setLabel('Показувати на сторінці партнерів');

    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('region')
            ->add('createdAt')
            ->add('updatedAt');
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['translations.name'])
            ->setEntityLabelInPlural('admin.dashboard.partner.list_title')
            ->setEntityLabelInSingular('admin.dashboard.partner.edit_button_title')
            ->setPageTitle(Crud::PAGE_NEW, 'admin.dashboard.partner.new_page_title')
            ->setPageTitle(Crud::PAGE_EDIT, 'admin.dashboard.partner.edit_page_title')
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
                Action::new('partnerExport', 'Export')
                    ->createAsGlobalAction()
                    ->linkToCrudAction('partnerExport')
                    ->addCssClass('btn btn-primary')
            );
    }

    /**
     * @param ExportService $exportService
     *
     * @return void
     */
    public function partnerExport(ExportService $exportService): void
    {
        $query = $this->getDoctrine()
            ->getRepository('App:Partner')
            ->createQueryBuilder('p')
            ->setMaxResults(1000)
            ->getQuery();

        $partnerList = $query->getResult();

        $exportData = [];

        /** @var Partner $partner */
        foreach ($partnerList as $partner) {

            $exportData[] = [
                'ID' => $partner->getId(),
                'Назва' => $partner->getName(),
                'Телефон' => $partner->getPhone(),
                'Eлектронна пошта' => $partner->getEmail(),
                'Сайт' => $partner->getSite(),
                'Facebook' => $partner->getFacebook(),
                'Twitter' => $partner->getTwitter(),
                'Youtube' => $partner->getYoutube(),
                'Telegram' => $partner->getTelegram(),
                'Instagram' => $partner->getInstagram(),
                'Адреса' => $partner->getAddress(),
                'Регіон' => !is_null($partner->getRegion()) ? $partner->getRegion()->getName() : '',
                'Текст' => $partner->getContent(),
                'Метатег title' => $partner->getMetaTitle(),
                'Метатег кeywords' => $partner->getKeywords(),
                'Метатег description' => $partner->getDescription(),
                'Дата створення' => $partner->getCreatedAt(),
                'Дата редагування' => $partner->getUpdatedAt(),
            ];
        }

        $exportService->export('partner', $exportData);
    }
}
