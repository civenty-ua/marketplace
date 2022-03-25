<?php

namespace App\Controller\Admin;

use App\Admin\Field\TranslationField;
use App\Admin\Field\VichImageField;
use App\Entity\Category;
use App\Entity\Course;
use App\Service\FileManager\FileManagerInterface;
use App\Service\FileManager\Mapping\CategoryBannerMapping;
use App\Service\FileManager\Mapping\CategoryImageMapping;
use Doctrine\ORM\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RequestStack;

class CategoryCrudController extends BaseCrudController
{
    private FileManagerInterface $fileManager;
    private ?string $currentLocale;


    public function __construct(
        FileManagerInterface $fileManager,
        RequestStack  $requestStack

    )
    {
        $this->fileManager = $fileManager;
        $this->currentLocale = $requestStack->getCurrentRequest()->getLocale();
    }

    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $fieldsConfig = [
            'name' => [
                'field_type' => TextType::class,
                'required' => true,
                'label' => 'Назва',
            ],
            'content' => [
                'field_type' => CKEditorType::class,
                'required' => false,
                'label' => 'Опис',
            ],
            'bottomContent' => [
                'field_type' => CKEditorType::class,
                'required' => false,
                'label' => 'Нижній контентний блок',
            ],
            'articleTitle' => [
                'field_type' => TextType::class,
                'required' => false,
                'label' => 'Назва Слайдеру статей',
            ],
            'learningTitle' => [
                'field_type' => TextType::class,
                'required' => false,
                'label' => 'Назва Слайдеру Навчальних матеріалів',
            ],
        ];

        $bannerPath = $this->fileManager->getUploadPath(CategoryBannerMapping::class);
        $imagePath = $this->fileManager->getUploadPath(CategoryImageMapping::class);

        yield IdField::new('id')->onlyOnIndex()->setLabel('ID');
        yield TextField::new('name')->onlyOnIndex()->setLabel('Назва')
            ->setColumns('col-sm-6 col-md-4');

        $banner = VichImageField::new('bannerFile')
            ->setLabel('Баннер')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms();
        if (Crud::PAGE_NEW === $pageName) {
            $banner->setRequired(true);
        } else {
            $banner->setRequired(false);
        }
        yield $banner;

        $image = ImageField::new('image')
            ->setUploadDir('public/' . $imagePath)
            ->setUploadedFileNamePattern('[timestamp]-[contenthash].[extension]')
            ->setBasePath($imagePath)
            ->setColumns('col-sm-6 col-md-4 hide-delete-btn')
            ->setLabel('Зображення');

        if (Crud::PAGE_NEW === $pageName) {
            $image->setRequired(true);
        } else {
            $image->setRequired(false);
        }

        yield $image;

        yield BooleanField::new('active')->setLabel('Включено')
            ->setColumns('col-sm-6 col-md-4');
        yield IntegerField::new('sort')->setLabel('Сортування')
            ->setColumns('col-sm-6 col-md-4');
        yield SlugField::new('slug')
            ->setTargetFieldName('slug')
            ->setLabel('Аліас URL')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms()
            ->setRequired(true);
        yield BooleanField::new('viewHomePage')->setLabel('Показувати на головній')
            ->setColumns('col-sm-6 col-md-4');
        yield BooleanField::new('viewInMenu')->setLabel('Показувати в меню')
            ->setColumns('col-sm-6 col-md-4');
        yield AssociationField::new('tags')
            ->setRequired(true)
            ->onlyOnForms()
            ->setLabel('Теги')
            ->setColumns('col-sm-6 col-md-4')
            ->setFormTypeOptions([
                'required' => false,
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
        yield AssociationField::new('courseBanner')
            ->setRequired(false)
            ->onlyOnForms()
            ->setLabel('Баннер з курсом')
            ->setColumns('col-sm-6 col-md-4')
            ->setFormTypeOptions([
                'required' => false,
                'by_reference' => true,
                'query_builder' => $this->getDoctrine()->getRepository(Course::class)
                    ->createQueryBuilder('c')
                    ->andWhere('c.isActive = true')->orderBy('c.createdAt', 'DESC')
            ]);
        yield TranslationField::new('translations', 'Переклади', $fieldsConfig)
            ->setRequired(true)
            ->hideOnIndex();
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['sort' => 'ASC'])
            ->setSearchFields(['translations.name'])
            ->setEntityLabelInPlural('admin.dashboard.directory.category.list_title')
            ->setEntityLabelInSingular('admin.dashboard.directory.category.edit_button_title')
            ->setPageTitle(Crud::PAGE_NEW, 'admin.dashboard.directory.category.new_page_title')
            ->setPageTitle(Crud::PAGE_EDIT, 'admin.dashboard.directory.category.edit_page_title')
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
