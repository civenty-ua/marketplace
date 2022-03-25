<?php

namespace App\Controller\Admin;

use App\Admin\Field\TranslationField;
use App\Entity\ExpertType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ExpertTypeCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return ExpertType::class;
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

        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name')->hideOnForm()->setLabel('Назва');
        yield DateField::new('createdAt')->hideOnForm()->setLabel('Дата створення');
        yield TranslationField::new('translations', 'Переклад', $fieldsConfig)
            ->setRequired(true)
            ->hideOnIndex();

    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setEntityLabelInPlural('admin.dashboard.directory.expert_type.list_title')
            ->setEntityLabelInSingular('admin.dashboard.directory.expert_type.edit_button_title')
            ->setPageTitle(Crud::PAGE_NEW, 'admin.dashboard.directory.expert_type.new_page_title')
            ->setPageTitle(Crud::PAGE_EDIT, 'admin.dashboard.directory.expert_type.edit_page_title')
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
}
