<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Market\MarketCrudController;
use App\Entity\TextBlocks;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TextBlocksCrudController extends MarketCrudController
{
    public static function getEntityFqcn(): string
    {
        return TextBlocks::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('TextTypeId'));
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('TextTypeId','Тип Тексту')
            ->setRequired(true)
            ->setHelp('Заповніть це поле')
            ->setColumns('col-sm-6 col-md-4');
       // yield TextField::new('symbolCode','Символьний код')
       //     ->setColumns('col-sm-6 col-md-4');
        yield TextField::new('text','Текст')
            ->setColumns('col-sm-6 col-md-4');
        yield TextField::new('textDescrtiption','Опис')
            ->setColumns('col-sm-6 col-md-4');
    }

    /**
     * @inheritdoc
     */
    protected function getMessagesDomain(): string
    {
        return 'textBlock';
    }

}
