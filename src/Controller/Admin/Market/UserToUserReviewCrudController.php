<?php

namespace App\Controller\Admin\Market;

use App\Repository\UserRepository;
use DateTime;
use App\Controller\Admin\UserCrudController;
use App\Entity\UserToUserReview;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\{AssociationField, DateTimeField, IdField, TextareaField};
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class UserToUserReviewCrudController extends MarketCrudController
{
    public AdminUrlGenerator $urlGenerator;

    public function __construct(AdminUrlGenerator $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return UserToUserReview::class;
    }

    public function configureActions(Actions $actions): Actions
    {   parent::configureActions($actions);

        return $actions->remove(Crud::PAGE_INDEX,Action::BATCH_DELETE);
    }

    public function configureFields(string $pageName): iterable
    {

        yield IdField::new('id')
            ->setLabel('ID')
            ->onlyOnIndex();
        $userField = AssociationField::new('user')
            ->setLabel('Користувач')
            ->formatValue(function ($value, UserToUserReview $userToUserReview) {
                $link = $this->urlGenerator
                    ->setController(UserCrudController::class)
                    ->setAction(Action::DETAIL)
                    ->set('entityId', $userToUserReview->getUser()->getId())
                    ->generateUrl();
                $linkTitle = $userToUserReview->getUser()->getName();

                return "<a href=\"$link\">$linkTitle</a>";
            })
            ->setFormTypeOptions([
                'query_builder' => function(UserRepository $repository) {
                    return $repository
                        ->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC');
                },
            ])
            ->setColumns('col-sm-6 col-md-4');

        $targetUserField = AssociationField::new('targetUser')
            ->setLabel('Постачальник Товарів/Послуг')
            ->formatValue(function ($value, UserToUserReview $userToUserReview) {
                $link = $this->urlGenerator
                    ->setController(UserCrudController::class)
                    ->setAction(Action::DETAIL)
                    ->set('entityId', $userToUserReview->getTargetUser()->getId())
                    ->generateUrl();
                $linkTitle = $userToUserReview->getTargetUser()->getName();

                return "<a href=\"$link\">$linkTitle</a>";
            })
            ->setFormTypeOptions([
                'query_builder' => function(UserRepository $repository) {
                    return $repository
                        ->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC');
                },
            ])
            ->setColumns('col-sm-6 col-md-4');
        if (Crud::PAGE_EDIT === $pageName) {
            $targetUserField->setFormTypeOption('disabled', true);
            $userField->setFormTypeOption('disabled', true);
        }
        yield $userField;
        yield $targetUserField;

        yield DateTimeField::new('createdAt','Дата Створення')
            ->setFormTypeOption('disabled',true)
            ->onlyOnIndex()
            ->setColumns('col-sm-6 col-md-4');

        yield TextareaField::new('reviewText')
            ->setLabel('Повідомлення')
            ->setColumns('col-sm-6 col-md-4')
            ->setFormTypeOptions(['attr' => ['maxlength' => 1000]]);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['id','user.name','targetUser.name']);
    }
    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
            ->add('id')
            ->add(EntityFilter::new('user')->setLabel('Відправник'))
            ->add(EntityFilter::new('targetUser')->setLabel('Отримувач'))
            ->add(DateTimeFilter::new('createdAt')->setLabel('Дата Створення'));
    }

    /**
     * @inheritdoc
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var UserToUserReview $entityInstance */
        $entityInstance->setCreatedAt(new DateTime('now'));
        $entityInstance->setUpdatedAt(new DateTime('now'));
        parent::persistEntity($entityManager, $entityInstance);
    }
    /**
     * @inheritdoc
     */
    protected function getMessagesDomain(): string
    {
        return 'userToUserReview';
    }
}
