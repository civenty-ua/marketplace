<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\ItemRepository;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\{Config\Action,
    Config\Actions,
    Config\Crud,
    Config\Assets,
    Controller\AbstractCrudController
};
use EasyCorp\Bundle\EasyAdminBundle\Field\{
    AssociationField,
    DateTimeField,
    IdField,
    TextareaField,
};
use Doctrine\ORM\EntityRepository;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use App\Entity\Comment;
use Symfony\Component\HttpFoundation\RequestStack;

class CommentCrudController extends BaseCrudController
{
    private ?string $currentLocale;

    public function __construct(RequestStack  $requestStack)
    {
        $this->currentLocale = $requestStack->getCurrentRequest()->getLocale();
    }

    public static function getEntityFqcn(): string
    {
        return Comment::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->onlyOnIndex();

        if (Crud::PAGE_NEW !== $pageName) {
            yield DateTimeField::new('createdAt')
                ->setLabel('Дата створення');
        }

        yield AssociationField::new('authorizedUser')
            ->setFormTypeOptions([
                'query_builder' => function(UserRepository $repository) {
                    return $repository
                        ->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC');
                },
            ])
            ->setLabel('Користувач');
        yield AssociationField::new('item')
            ->setLabel("Зв'язаний елемент");
        yield TextareaField::new('message')
            ->setLabel('Повідомлення')
            ->setFormType(CKEditorType::class);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE);

    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields([
                'authorizedUser.email',
                'item.id',
            ])
            ->setEntityLabelInPlural('admin.dashboard.comment.list_title')
            ->setEntityLabelInSingular('admin.dashboard.comment.edit_button_title')
            ->setPageTitle(Crud::PAGE_NEW, 'admin.dashboard.comment.new_page_title')
            ->setPageTitle(Crud::PAGE_EDIT, 'admin.dashboard.comment.edit_page_title')
            ->setFormOptions(
                ['validation_groups' => ['Default', 'creation']],
                ['validation_groups' => ['Default', 'creation']]
            )
            ->setFormThemes([
                '@A2lixTranslationForm/bootstrap_4_layout.html.twig',
                '@FOSCKEditor/Form/ckeditor_widget.html.twig',
                '@EasyAdmin/crud/form_theme.html.twig',
            ])
            ->showEntityActionsInlined();
    }
}
