<?php

namespace App\Controller\Admin\Market;

use App\Entity\Market\Notification\SystemMessage;
use EasyCorp\Bundle\EasyAdminBundle\Field\{AssociationField, NumberField, TextareaField, TextField};
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;


/**
 * BidOfferCrudController.
 */
class SystemMessageCrudController extends NotificationCrudController
{

    /**
     * @inheritdoc
     */
    public static function getEntityFqcn(): string
    {
        return SystemMessage::class;
    }

    /**
     * @inheritdoc
     */
    public function configureFields(string $pageName): iterable
    {
        foreach (parent::configureFields($pageName) as $field) {
            if ($field->getAsDto()->getProperty() != 'sender') {
                yield $field;
            }
        }
        yield TextareaField::new('message')
            ->setLabel('Повідомлення')
            ->setColumns('col-sm-6 col-md-4');
    }

    public function getMessagesDomain(): string
    {
        return 'systemMessage';
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields([
                'id',
                'receiver.name',
            ]);
    }
}
