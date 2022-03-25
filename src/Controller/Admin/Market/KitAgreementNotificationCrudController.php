<?php

namespace App\Controller\Admin\Market;

use App\Entity\Market\Notification\KitAgreementNotification;
use EasyCorp\Bundle\EasyAdminBundle\Field\{AssociationField, TextareaField, TextField};
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

/**
 * DealOfferCrudController.
 */
class KitAgreementNotificationCrudController extends NotificationCrudController
{

    /**
     * @inheritdoc
     */
    public static function getEntityFqcn(): string
    {
        return KitAgreementNotification::class;
    }

    /**
     * @inheritdoc
     */
    public function configureFields(string $pageName): iterable
    {
        foreach (parent::configureFields($pageName) as $field){
            yield $field;
        }
        yield TextareaField::new('message')
            ->setLabel('Повідомлення')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms();
        yield AssociationField::new('commodity')
            ->setLabel('Товар')
            ->setColumns('col-sm-6 col-md-4');
        yield TextField::new('name')
            ->setLabel('Ім\'я')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms();
        yield TextField::new('phone')
            ->setLabel('Телефон')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms();

    }

    public function configureActions(Actions $actions): Actions
    {
        parent::configureActions($actions);
        $actions->remove(Crud::PAGE_INDEX,'new');
        return $actions;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields([
                'id',
                'sender.name',
                'receiver.name',
                'commodity.title'
            ]);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
            ->add(EntityFilter::new('commodity')->setLabel('Продукт чи Послуга'));
    }

    public function getMessagesDomain(): string
    {
        return 'kitAgreementNotification';
    }
}
