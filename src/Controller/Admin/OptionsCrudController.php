<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\{Assets, Actions, Action, Crud, Filters};
use EasyCorp\Bundle\EasyAdminBundle\Field\{IdField, ImageField, TextField, TextareaField};
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use App\Entity\Options;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RequestStack;

class OptionsCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return Options::class;
    }

    protected $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function configureFields(string $pageName): iterable
    {
        $kernelDir = $this->get('parameter_bag')->get('kernel.project_dir');
        $dir = $kernelDir . '/public/upload/options';
        $fs = new Filesystem();
        $fs->mkdir($dir);
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('code')
            ->setLabel('Код налаштування')
            ->setColumns('col-sm-6 col-md-4')
            ->setFormTypeOption('disabled', 'disabled');
        $value = TextField::new('value')
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('Значення');

        if (Crud::PAGE_EDIT == $pageName) {
            $this->checkValueIsRequiredField()
                ? yield $value->setRequired(false)
                : yield $value->setRequired(true);
        } else {
            yield $value;
        }

        yield TextField::new('description')
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('Опис');
        $img = ImageField::new('imageName')
            ->setUploadDir('public/upload/options')
            ->setUploadedFileNamePattern('[timestamp]-[contenthash].[extension]')
            ->setBasePath('upload/options')
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('Зображення');
        yield $img->setRequired(false);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['id' => 'ASC'])
            ->setEntityLabelInPlural('admin.dashboard.option.list_title')
            ->setEntityLabelInSingular('admin.dashboard.option.edit_button_title')
            ->setPageTitle(Crud::PAGE_NEW, 'admin.dashboard.option.new_page_title')
            ->setPageTitle(Crud::PAGE_EDIT, 'admin.dashboard.option.edit_page_title')
            ->setSearchFields(['code', 'description', 'value'])
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

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('code')
            ->add('value')
            ->add('description');
    }

    public function checkValueIsRequiredField()
    {
        $request = $this->requestStack->getCurrentRequest();
        $optionId = $request->query->get('entityId');
        $option = $this->getDoctrine()->getRepository(Options::class)->findOneBy(['id' => $optionId]);

        return str_starts_with($option->getCode(), 'homepage_footer_');
    }
}
