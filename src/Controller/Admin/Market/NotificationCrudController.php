<?php

namespace App\Controller\Admin\Market;

use App\Entity\Market\Notification\Notification;
use EasyCorp\Bundle\EasyAdminBundle\Field\{AssociationField,
    BooleanField,
    DateTimeField,
    IdField,
    TextareaField
};
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
/**
 * BidOfferCrudController.
 */
class NotificationCrudController extends MarketCrudController
{

    /**
     * @inheritdoc
     */
    public static function getEntityFqcn(): string
    {
        return Notification::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);

        return $actions
            ->remove(Crud::PAGE_DETAIL, Action::DELETE);
    }
    /**
     * @inheritdoc
     */
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->setLabel('ID')
            ->onlyOnIndex();
        yield BooleanField::new('isRead')
            ->setLabel('Прочитано')
            ->setFormTypeOption('disabled', true)
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms();
        yield BooleanField::new('isSoftDeleted')
            ->setLabel('Отримувач видалив повідомлення')
            ->setFormTypeOption('disabled', true)
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms();
        yield AssociationField::new('sender')
            ->setLabel('Відправник')
            ->setColumns('col-sm-6 col-md-4');
        yield AssociationField::new('receiver')
            ->setLabel('Отримувач')
            ->setColumns('col-sm-6 col-md-4');
        yield TextareaField::new('title', 'Тема')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms();
        yield BooleanField::new('isRead')
            ->setLabel('Прочитано')
            ->setFormTypeOption('disabled', true)
            ->setColumns('col-sm-6 col-md-4');
        yield DateTimeField::new('createdAt')
            ->setLabel('Дата Створення')
            ->onlyOnIndex();
        yield BooleanField::new('isSoftDeleted')
            ->setLabel('Отримувач видалив повідомлення')
            ->setFormTypeOption('disabled', true);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var Notification $entityInstance */
        $entityInstance->setIsActive(true);
        $entityInstance->setOfferReviewNotificationSent(false);
        $entityInstance->setCreatedAt(new \DateTime('now'));
        $entityInstance->setUpdatedAt(new \DateTime('now'));
        $entityInstance->setIsSoftDeleted(false);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function getMessagesDomain(): string
    {
        return '';
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields([
                'id',
                'sender.name',
                'receiver.name',
            ]);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
            ->add('id')
            ->add(EntityFilter::new('sender')->setLabel('Відправник'))
            ->add(EntityFilter::new('receiver')->setLabel('Отримувач'))
            ->add(BooleanFilter::new('isRead')->setLabel('Прочитано'))
            ->add(DateTimeFilter::new('createdAt')->setLabel('Дата Створення'))
            ->add(BooleanFilter::new('isSoftDeleted')->setLabel('Отримувач видалив повідомлення'))
            ;
    }
}
