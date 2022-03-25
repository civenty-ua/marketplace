<?php
declare(strict_types = 1);

namespace App\Controller\Admin\Market;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\{
    IdField,
    TextField,
};
use App\Admin\Field\Market\{
    CategoryField,
    CategoryAttributesField,
};
use App\Entity\Market\{
    Category,
    Commodity,
};
/**
 * CategoryProductCrudController.
 */
class CategoryProductCrudController extends CategoryCrudController
{
    /**
     * @inheritdoc
     */
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }
    /**
     * @inheritdoc
     */
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->setLabel('admin.market.category.id')
            ->onlyOnIndex();
        yield TextField::new('title')
            ->setLabel('admin.market.category.title');
        yield CategoryField::new(
                'parent',
                'admin.market.category.parent',
                Commodity::TYPE_PRODUCT,
                $this->getDoctrine()
            );
        yield CategoryAttributesField::new(self::CATEGORY_ATTRIBUTES_PROPERTY_NAME)
            ->setLabel('admin.market.category.attributes')
            ->setColumns('col-md-8 col-xxl-7')
            ->onlyOnForms();
    }
    /**
     * @inheritdoc
     */
    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['title']);
    }
    /**
     * @inheritdoc
     */
    protected function getMessagesDomain(): string
    {
        return 'categoryProduct';
    }
    /**
     * @inheritdoc
     */
    protected function getCategoryCommodityType(): string
    {
        return Commodity::TYPE_PRODUCT;
    }
}
