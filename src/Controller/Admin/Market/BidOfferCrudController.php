<?php

namespace App\Controller\Admin\Market;

use EasyCorp\Bundle\EasyAdminBundle\Field\{AssociationField, NumberField, TextareaField, TextField};
use App\Entity\Market\Notification\BidOffer;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;


/**
 * BidOfferCrudController.
 */
class BidOfferCrudController extends NotificationCrudController
{

    /**
     * @inheritdoc
     */
    public static function getEntityFqcn(): string
    {
        return BidOffer::class;
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
        yield NumberField::new('price')
            ->setLabel('Ціна')
            ->setColumns('col-sm-6 col-md-4');
        yield TextField::new('name')
            ->setLabel('Ім\'я')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms();
        yield TextField::new('phone')
            ->setLabel('Телефон')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms();
        yield TextField::new('email')
            ->setLabel('Email')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms();

    }

    public function getMessagesDomain(): string
    {
        return 'bidOffer';
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
}
