<?php
declare(strict_types=1);

namespace App\Controller\Admin\Market;

use EasyCorp\Bundle\EasyAdminBundle\{
    Context\AdminContext,
    Config\Crud,
    Config\KeyValueStore,
    Dto\EntityDto,
    Field\IdField,
    Field\TextField,
    Field\TextareaField,
    Field\ChoiceField,
};
use App\Entity\Market\{
    Attribute,
    Category,
    Commodity,
    CommodityProduct,
    CommodityService,
};
/**
 * AttributeCrudController.
 */
class AttributeCrudController extends MarketCrudController
{
    /**
     * @inheritDoc
     */
    public static function getEntityFqcn(): string
    {
        return Attribute::class;
    }
    /**
     * @inheritDoc
     */
    public function configureFields(string $pageName): iterable
    {
        $typesList          = [];
        $dictionariesList   = [];
        $specialCodesList   = [];

        foreach (Attribute::getAvailableTypes() as $availableType) {
            $title = "admin.market.attribute.types.$availableType";
            $typesList[$title] = $availableType;
        }
        foreach (array_keys(Attribute::DICTIONARIES_DATA) as $dictionary) {
            $title = "admin.market.attribute.dictionaries.$dictionary";
            $dictionariesList[$title] = $dictionary;
        }
        foreach (Attribute::getAvailableSpecialCodes() as $code) {
            $title = "admin.market.attribute.codes.$code";
            $specialCodesList[$title] = $code;
        }

        yield IdField::new('id')
            ->setLabel('admin.market.attribute.id')
            ->onlyOnIndex();
        yield TextField::new('title')
            ->setLabel('admin.market.attribute.title');
        yield TextareaField::new('description')
            ->setLabel('admin.market.attribute.description');
        yield ChoiceField::new('type')
            ->setLabel('admin.market.attribute.type')
            ->addCssClass('attribute-edit-attribute-type')
            ->setChoices($typesList);
        yield ChoiceField::new('dictionary')
            ->setLabel('admin.market.attribute.dictionary')
            ->addCssClass('attribute-edit-attribute-dictionary')
            ->setChoices($dictionariesList);
        yield ChoiceField::new('code')
            ->setLabel('admin.market.attribute.code')
            ->setChoices($specialCodesList);
    }
    /**
     * @inheritDoc
     */
    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['title'])
            ->overrideTemplate(
                'crud/edit',
                'admin/market/attribute/edit_form.html.twig'
            );
    }
    /**
     * @inheritdoc
     */
    public function edit(AdminContext $context)
    {
        /**
         * @var KeyValueStore|mixed $result
         * @var EntityDto           $entityDto
         * @var Attribute           $attribute
         */
        $result     = parent::edit($context);

        if (!($result instanceof KeyValueStore)) {
            return $result;
        }

        $entityDto  = $result->get('entity');
        $attribute  = $entityDto->getInstance();

        $result->set('commoditiesInUse', $this->getCommoditiesWithUsedAttribute($attribute));

        return $result;
    }
    /**
     * @inheritdoc
     */
    protected function getMessagesDomain(): string
    {
        return 'attribute';
    }
    /**
     * Get commodities set, witch use given attribute.
     *
     * @param Attribute $attribute Attribute.
     *
     * @return Commodity[] Commodities set.
     */
    private function getCommoditiesWithUsedAttribute(Attribute $attribute): array
    {
        $categories = $this->getCategoriesWithUsedAttribute($attribute);
        $result     = [];

        foreach ([
            CommodityProduct::class,
            CommodityService::class,
        ] as $repository) {
            $commodities = $this
                ->getDoctrine()
                ->getRepository($repository)
                ->findBy([
                    'category' => $categories,
                ]);

            $result = array_merge($result, $commodities);
        }

        return $result;
    }
    /**
     * Get categories set, witch use given attribute.
     *
     * @param Attribute $attribute Attribute.
     *
     * @return Category[] Categories set.
     */
    private function getCategoriesWithUsedAttribute(Attribute $attribute): array
    {
        $aliasRoot          = 'category';
        $aliasParameters    = 'categoryAttributesParameters';
        $queryBuilder       = $this
            ->getDoctrine()
            ->getRepository(Category::class)
            ->createQueryBuilder($aliasRoot);

        $queryBuilder
            ->leftJoin("$aliasRoot.categoryAttributesParameters", $aliasParameters)
            ->where(
                $queryBuilder->expr()->eq("$aliasParameters.attribute", $attribute->getId())
            );

        return $queryBuilder->getQuery()->getResult();
    }
}
