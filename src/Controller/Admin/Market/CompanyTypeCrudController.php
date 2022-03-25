<?php

namespace App\Controller\Admin\Market;

use App\Entity\Market\CompanyType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\{
    BooleanField,
    IdField,
    TextField,
    TextareaField,
    ChoiceField,
};
use App\Entity\Market\Attribute;

class CompanyTypeCrudController extends MarketCrudController
{
    public static function getEntityFqcn(): string
    {
        return CompanyType::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->setLabel('Id')
            ->onlyOnIndex();
        yield TextField::new('name')
            ->setLabel('Назва');
        yield ChoiceField::new('typeRole')
            ->setLabel('Роль')
            ->setColumns('col-sm-6')
            ->autocomplete()
            ->setChoices([
                'Оптовий покупець' => 'wholesale-bayer',
                'Продавець' => 'salesman',
                'Постачальник послуг' => 'service-provider',
            ])
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPageTitle(Crud::PAGE_INDEX, "Вид компанії")
            ->setPageTitle(Crud::PAGE_NEW, "Вид компанії")
            ->setPageTitle(Crud::PAGE_EDIT, "Вид компанії")
            ->setEntityLabelInPlural("Види компаній")
            ->setEntityLabelInSingular("Вид компанії")
            ->setDefaultSort(['id' => 'ASC'])
            ->setSearchFields(['typeRole']);
    }
    /**
     * @inheritdoc
     */
    protected function getMessagesDomain(): string
    {
        return 'market';
    }
}
