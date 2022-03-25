<?php


namespace App\Controller\Admin;


use App\Admin\Field\EmptyField;
use App\Admin\Field\TranslationField;
use App\Entity\Contact;
use Doctrine\ORM\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RequestStack;

class ContactCrudController extends BaseCrudController
{
    private ?string $currentLocale;

    public function __construct(RequestStack $requestStack)
    {
        $this->currentLocale = $requestStack->getCurrentRequest()->getLocale();
    }

    public static function getEntityFqcn(): string
    {
        return Contact::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $fieldsConfig = [
            'title' => [
                'field_type' => TextType::class,
                'required' => true,
                'label' => 'Назва',
            ],
            'address' => [
                'field_type' => TextType::class,
                'required' => true,
                'label' => 'Адреса',
            ],
            'head' => [
                'field_type' => TextType::class,
                'required' => false,
                'label' => 'ПІБ Директора',
            ],
            'position' => [
                'field_type' => TextType::class,
                'required' => false,
                'label' => 'Посада контактної особи',
            ],
            'fullname' => [
                'field_type' => TextType::class,
                'required' => false,
                'label' => 'ПІБ контактної особи',
            ],

        ];

        $kernelDir = $this->get('parameter_bag')->get('kernel.project_dir');
        $dir = $kernelDir . '/public/upload/contact';
        $fs = new Filesystem();
        $fs->mkdir($dir);

        yield IdField::new('id')->onlyOnIndex();

        yield TranslationField::new('translations', 'Переклади', $fieldsConfig)
            ->setRequired(true)
            ->hideOnIndex();

        $img = ImageField::new('image')
            ->setUploadDir('public/upload/contact')
            ->setUploadedFileNamePattern('[timestamp]-[contenthash].[extension]')
            ->setBasePath('upload/contact')
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('Зображення');
        if (Crud::PAGE_NEW === $pageName) {
            yield $img->setRequired(true);
        } else {
            yield $img->setRequired(false);
        }
        yield TextField::new('email', 'Email')
            ->setColumns('col-sm-6 col-md-4')
            ->setRequired(false);
        yield TextField::new('site', 'Сайт')
            ->setColumns('col-sm-6 col-md-4')->setRequired(false);
        yield TextField::new('phone', 'Телефон')
            ->setColumns('col-sm-6 col-md-4')->setRequired(false);
        yield TextField::new('cellPhone', 'Моб Телефон контактної особи')
            ->setColumns('col-sm-6 col-md-4')->setRequired(false);
        yield AssociationField::new('region')
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
            ])
            ->setLabel('form_registration.region');
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['email'])
            ->setEntityLabelInPlural('admin.dashboard.contact.list_title')
            ->setEntityLabelInSingular('admin.dashboard.contact.edit_button_title')
            ->setPageTitle(Crud::PAGE_NEW, 'admin.dashboard.contact.new_page_title')
            ->setPageTitle(Crud::PAGE_EDIT, 'admin.dashboard.contact.edit_page_title')
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
}