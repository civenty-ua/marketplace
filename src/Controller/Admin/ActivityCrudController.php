<?php

namespace App\Controller\Admin;

use App\Admin\Field\TranslationField;
use App\Entity\Activity;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ActivityCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return Activity::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $fieldsConfig = [
            'name' => [
                'field_type' => TextType::class,
                'required' => true,
                'label' => 'Назва',
            ],
        ];

        yield IdField::new('id')->onlyOnIndex()->setLabel('ID');
        yield TextField::new('name')->onlyOnIndex()->setLabel('Назва');
        yield TranslationField::new('translations', 'Переклади', $fieldsConfig)
            ->setRequired(true)
            ->hideOnIndex();

    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['id' => 'ASC'])
            ->setSearchFields(['translations.name'])
            ->setEntityLabelInPlural('admin.dashboard.directory.activity.list_title')
            ->setEntityLabelInSingular('admin.dashboard.directory.activity.edit_button_title')
            ->setPageTitle(Crud::PAGE_NEW, 'admin.dashboard.directory.activity.new_page_title')
            ->setPageTitle(Crud::PAGE_EDIT, 'admin.dashboard.directory.activity.edit_page_title')
            ->setFormThemes(
                [
                    '@A2lixTranslationForm/bootstrap_4_layout.html.twig',
                    '@EasyAdmin/crud/form_theme.html.twig',
                ]
            )
            ->showEntityActionsInlined();
    }
}
