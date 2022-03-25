<?php

namespace App\Controller\Admin;

use App\Admin\Field\EmptyField;
use App\Admin\Field\TranslationField;
use App\Entity\Page;
use App\Repository\TypePageRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PageCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return Page::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $fieldsConfig = [
            'title' => [
                'field_type' => TextType::class,
                'required' => true,
                'label' => 'Заголовок',
                'row_attr' => [
                    'class' => 'col-md-4',
                ],
                'block_name' => 'title',
                'block_prefix' => 'translate',
            ],
            'metaTitle' => [
                'field_type' => TextType::class,
                'required' => false,
                'label' => 'Метатег title',
                'row_attr' => [
                    'class' => 'col-md-4',
                ],
            ],
            'keywords' => [
                'field_type' => TextType::class,
                'required' => false,
                'label' => 'Метатег кeywords',
                'row_attr' => [
                    'class' => 'col-md-4',
                ],
            ],
            'description' => [
                'field_type' => TextareaType::class,
                'required' => false,
                'label' => 'Метатег description',
                'row_attr' => [
                    'class' => 'col-md-4 item-meta-description',
                ],
                'attr' => [
                    'class' => 'item-meta-description',
                ],
            ],
            'short' => [
                'field_type' => TextareaType::class,
                'required' => false,
                'label' => 'Анонс',
                'row_attr' => [
                    'class' => 'col-md-8 float-right padding-left-short',
                ],
                'attr' => [
                    'class' => 'item-short'
                ]
            ],
            'content' => [
                'field_type' => CKEditorType::class,
                'required' => false,
                'label' => 'Текст',
                'row_attr' => [
                    'class' => 'col-md-12 float',
                ],
            ]
        ];

        $kernelDir = $this->get('parameter_bag')->get('kernel.project_dir');
        $dir = $kernelDir . '/public/upload/business-tools';
        $fs = new Filesystem();
        $fs->mkdir($dir);
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('title')->setLabel('Назва')->onlyOnIndex();
        yield TextField::new('alias')->setLabel('Алиас')->onlyOnIndex();
        yield DateField::new('createdAt')->setLabel('Дата створення')->onlyOnIndex();

        yield SlugField::new('alias')
            ->setTargetFieldName('alias')
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('Аліас URL')
            ->setRequired(false)
            ->onlyOnForms();

        yield DateField::new('createdAt')->setLabel('Дата створення')->onlyOnIndex()
            ->hideOnIndex();

        $img = ImageField::new('imageName')
            ->setUploadDir('public/upload/business-tools')
            ->setUploadedFileNamePattern('[timestamp]-[contenthash].[extension]')
            ->setBasePath('upload/business-tools')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms()
            ->setLabel('Зображення');
        if (Crud::PAGE_NEW === $pageName) {
            yield $img->setRequired(true);
        } else {
            yield $img->setRequired(false);
        }


        yield AssociationField::new('typePage')
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('Тип сторінки')->setFormTypeOptions([
                'by_reference' => true,
                'query_builder' => function (TypePageRepository $typePage) {
                    return $typePage->createQueryBuilder('tp')
                        ->andWhere('tp.code IN (:list)')
                        ->setParameter('list', ['business_tools']);
                },
            ]);
        yield EmptyField::new();
        //show only types of page, what names you set in array in method setParameter.

        yield TranslationField::new('translations', 'Переклад', $fieldsConfig)
            ->setRequired(true)
            ->onlyOnForms();

        yield EmptyField::new();
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['translations.title'])
            ->setEntityLabelInPlural('admin.dashboard.page.list_title')
            ->setEntityLabelInSingular('admin.dashboard.page.edit_button_title')
            ->setPageTitle(Crud::PAGE_NEW, 'admin.dashboard.page.new_page_title')
            ->setPageTitle(Crud::PAGE_EDIT, 'admin.dashboard.page.edit_page_title')
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
