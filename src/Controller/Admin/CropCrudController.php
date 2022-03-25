<?php

namespace App\Controller\Admin;

use App\Admin\Field\TranslationField;
use App\Entity\Crop;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CropCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return Crop::class;
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
        ];

        $kernelDir = $this->get('parameter_bag')->get('kernel.project_dir');
        $dir = $kernelDir . '/public/upload/crop';
        $fs = new Filesystem();
        $fs->mkdir($dir);

        yield IdField::new('id')->onlyOnIndex()->setLabel('ID');
        yield TextField::new('name')->onlyOnIndex()->setLabel('Назва');
        $img = ImageField::new('imageName')
            ->setUploadDir('public/upload/crop')
            ->setUploadedFileNamePattern('[timestamp]-[contenthash].[extension]')
            ->setBasePath('upload/crop')
            ->setLabel('Зображення')
            ->setColumns('col-sm-6 col-md-4');
        if (Crud::PAGE_NEW === $pageName) {
            yield $img->setRequired(true);
        } else {
            yield $img->setRequired(false);
        }

        yield TranslationField::new('translations', 'Переклади', $fieldsConfig)
            ->setRequired(true)
            ->hideOnIndex();
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setSearchFields(['translations.name'])
            ->setDefaultSort(['id' => 'ASC'])
            ->setEntityLabelInPlural('admin.dashboard.directory.crop.list_title')
            ->setEntityLabelInSingular('admin.dashboard.directory.crop.edit_button_title')
            ->setPageTitle(Crud::PAGE_NEW, 'admin.dashboard.directory.crop.new_page_title')
            ->setPageTitle(Crud::PAGE_EDIT, 'admin.dashboard.directory.crop.edit_page_title')
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
