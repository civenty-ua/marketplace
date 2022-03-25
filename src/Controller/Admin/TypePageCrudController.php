<?php

namespace App\Controller\Admin;

use App\Admin\Field\TranslationField;
use App\Entity\TypePage;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TypePageCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return TypePage::class;
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
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['translations.name'])
            ->setEntityLabelInPlural('admin.dashboard.directory.type_page.list_title')
            ->setEntityLabelInSingular('admin.dashboard.directory.type_page.edit_button_title')
            ->setPageTitle(Crud::PAGE_NEW, 'admin.dashboard.directory.type_page.new_page_title')
            ->setPageTitle(Crud::PAGE_EDIT, 'admin.dashboard.directory.type_page.edit_page_title')
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
            ->disable(Action::NEW, Action::DELETE);
    }

}
