<?php

namespace App\Controller\Admin;

use App\Admin\Field\TranslationField;
use App\Entity\FeedbackForm;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Form\Feedback\FeedbackFormQuestionType;

class FeedbackFormCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return FeedbackForm::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield TextField::new('title')->hideOnForm()->setLabel('Назва');
        yield TranslationField::new('translations',
            'Переклади',
            [
                'title' => [
                    'field_type' => TextType::class,
                    'required' => true,
                    'label' => 'Назва',
                    'row_attr' => [
                        'class' => 'col-md-4'
                    ]
                ],
                'keywords' => [
                    'field_type' => TextType::class,
                    'required' => false,
                    'label' => 'Метатег keywords',
                    'row_attr' => [
                        'class' => 'col-md-4'
                    ]
                ],
                'description' => [
                    'field_type' => TextareaType::class,
                    'required' => false,
                    'label' => 'Метатег description',
                    'row_attr' => [
                        'class' => 'col-md-4'
                    ]
                ],
            ])
            ->setRequired(true)
            ->hideOnIndex();
        yield CollectionField::new('feedbackFormQuestions', 'Питання')
            ->allowAdd(true)
            ->allowDelete(true)
            ->setEntryIsComplex(true)
            ->setSortable(false)
            ->setEntryType(FeedbackFormQuestionType::class);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('items');
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['translations.title'])
            ->setEntityLabelInPlural('admin.dashboard.feedback_form.list_title')
            ->setEntityLabelInSingular('admin.dashboard.feedback_form.edit_button_title')
            ->setPageTitle(Crud::PAGE_NEW, 'admin.dashboard.feedback_form.new_page_title')
            ->setPageTitle(Crud::PAGE_EDIT, 'admin.dashboard.feedback_form.edit_page_title')
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
