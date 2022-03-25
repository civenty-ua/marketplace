<?php

namespace App\Controller\Admin;

use App\Admin\Field\TranslationField;
use App\Entity\Review;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ReviewCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return Review::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $fieldsConfig = [
            'name' => [
                'field_type' => TextType::class,
                'required' => true,
                'label' => "Ім'я",
            ],
            'position' => [
                'field_type' => TextType::class,
                'required' => false,
                'label' => "Посада",
            ],
            'content' => [
                'field_type' => CKEditorType::class,
                'required' => false,
                'label' => 'Відгук',
            ],
        ];
        yield TranslationField::new('name', "Ім'я")->onlyOnIndex();
        yield TranslationField::new('position', 'Посада')->onlyOnIndex();
        yield BooleanField::new('isTop','Відображати на головній')->onlyOnForms();
        yield TranslationField::new('translations', 'Переклад', $fieldsConfig)
            ->setRequired(true)
            ->hideOnIndex();

    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['translations.name'])
            ->setEntityLabelInPlural('admin.dashboard.review.list_title')
            ->setEntityLabelInSingular('admin.dashboard.review.edit_button_title')
            ->setPageTitle(Crud::PAGE_NEW, 'admin.dashboard.review.new_page_title')
            ->setPageTitle(Crud::PAGE_EDIT, 'admin.dashboard.review.edit_page_title')
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
