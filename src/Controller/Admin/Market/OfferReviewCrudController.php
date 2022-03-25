<?php

namespace App\Controller\Admin\Market;

use App\Controller\Admin\BaseCrudController;
use App\Entity\Market\Notification\OfferReview;
use EasyCorp\Bundle\EasyAdminBundle\Field\{
    AssociationField,
    BooleanField,
    IdField,
    TextField,
};
/**
 * OfferReviewCrudController.
 */
class OfferReviewCrudController extends BaseCrudController
{
    /**
     * @inheritdoc
     */
    public static function getEntityFqcn(): string
    {
        return OfferReview::class;
    }

    /**
     * @inheritdoc
     */
    public function configureFields(string $pageName): iterable
    {

        yield IdField::new('id')
            ->setLabel('ID')
            ->onlyOnIndex();
        yield AssociationField::new('sender')
            ->setLabel('Отримувач')
            ->setColumns('col-sm-6 col-md-4');
        yield AssociationField::new('receiver')
            ->setLabel('Відправник')
            ->setColumns('col-sm-6 col-md-4');
        yield BooleanField::new('isRead')
            ->setLabel('Прочитано')
            ->setColumns('col-sm-6 col-md-4');
        yield AssociationField::new('parentNotification')
            ->setLabel('Операція для опитування')
            ->setColumns('col-sm-6 col-md-4');
        yield TextField::new('message')
            ->setLabel('Повідомлення')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms();


    }
}
