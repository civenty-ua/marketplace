<?php

namespace App\Controller\Admin\Market;

use App\Entity\Market\Phone;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\{
    BooleanField,
    IdField,
    TextField,
};

class PhoneCrudController extends MarketCrudController
{
    public static function getEntityFqcn(): string
    {
        return Phone::class;
    }

    public function configureFields(string $pageName): iterable
    {

        yield IdField::new('id')
            ->setLabel('ID')
            ->onlyOnIndex();
        yield TextField::new('user', 'Ім`я')
            ->formatValue(function ($value, Phone $phone):string {
                return $phone
                    ? "{$phone->getUser()->getName()} [{$phone->getUser()->getId()}]"
                    : '';
            });
        yield TextField::new('phone')
            ->setLabel('admin.market.phone.phone');
        yield BooleanField::new('isMain')
            ->setLabel('admin.market.phone.main');
        yield BooleanField::new('isTelegram')
            ->setLabel('admin.market.phone.telegram');
        yield BooleanField::new('isViber')
            ->setLabel('admin.market.phone.viber');
        yield BooleanField::new('isWhatsApp')
            ->setLabel('admin.market.phone.whatsApp');
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setDefaultSort(['id' => 'ASC'])
            ->setSearchFields(['id']);
    }
    /**
     * @inheritdoc
     */
    protected function getMessagesDomain(): string
    {
        return 'phone';
    }
}
