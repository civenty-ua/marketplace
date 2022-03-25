<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Market\MarketCrudController;
use App\Entity\TextType;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TextTypeCrudController extends MarketCrudController
{
    public static function getEntityFqcn(): string
    {
        return TextType::class;
    }


    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name','Назва');
    }

    /**
     * @inheritdoc
     */
    protected function getMessagesDomain(): string
    {
        return 'textType';
    }
}
