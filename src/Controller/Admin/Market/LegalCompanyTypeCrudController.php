<?php

namespace App\Controller\Admin\Market;

use App\Entity\Market\LegalCompanyType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\{
    IdField,
    TextField,
};

class LegalCompanyTypeCrudController extends MarketCrudController
{
    public static function getEntityFqcn(): string
    {
        return LegalCompanyType::class;
    }

    public function configureFields(string $pageName): iterable
    {

        yield IdField::new('id')
            ->setLabel('Id')
            ->onlyOnIndex();
        yield TextField::new('name')
            ->setLabel('Назва');
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPageTitle(Crud::PAGE_INDEX, "Юридичний тип компанії")
            ->setPageTitle(Crud::PAGE_NEW, "Юридичний тип компанії")
            ->setPageTitle(Crud::PAGE_EDIT, "Юридичний тип компанії")
            ->setEntityLabelInPlural("Юридичні типи компаній")
            ->setEntityLabelInSingular("Юридичний тип компанії")
            ->setDefaultSort(['id' => 'ASC'])
            ->setSearchFields(['name']);
    }
    /**
     * @inheritdoc
     */
    protected function getMessagesDomain(): string
    {
        return 'market';
    }
}
