<?php

namespace App\Controller\Admin;

use App\Admin\Field\TranslationField;
use App\Entity\Tags;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TagsCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return Tags::class;
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

        // Index
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name')->hideOnForm()->setLabel('Назва');
        yield SlugField::new('slug')
            ->setTargetFieldName('slug')
            ->setLabel('Аліас URL')
            ->setRequired(false)
            ->onlyOnIndex();
        yield DateField::new('createdAt')->hideOnForm()->setLabel('Дата створення');

        // Form
        yield TranslationField::new('translations', 'Переклад', $fieldsConfig)
            ->setRequired(true)
            ->hideOnIndex();
        yield SlugField::new('slug')
            ->setTargetFieldName('slug')
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('Аліас URL')
            ->setRequired(false)
            ->onlyOnForms();
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['translations.name'])
            ->setEntityLabelInPlural('admin.dashboard.directory.tag.list_title')
            ->setEntityLabelInSingular('admin.dashboard.directory.tag.edit_button_title')
            ->setPageTitle(Crud::PAGE_NEW, 'admin.dashboard.directory.tag.new_page_title')
            ->setPageTitle(Crud::PAGE_EDIT, 'admin.dashboard.directory.tag.edit_page_title')
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
