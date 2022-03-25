<?php
declare(strict_types = 1);

namespace App\Controller\Admin\Market;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use App\Entity\Market\{
    Attribute,
    Category,
    CategoryAttributeParameters,
    CategoryAttributeListValue,
};
/**
 * CategoryCrudController.
 */
abstract class CategoryCrudController extends MarketCrudController
{
    protected const CATEGORY_ATTRIBUTES_PROPERTY_NAME = 'categoryAttributesParameters';
    /**
     * @inheritdoc
     */
    public function index(AdminContext $context)
    {
        $this->setIndexPageDefaultFilter($context, 'commodityType', [
            'comparison'    => 'like',
            'value'         => $this->getCategoryCommodityType(),
        ]);

        return parent::index($context);
    }
    /**
     * @inheritdoc
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->setCategoryAttributes(
            $entityManager,
            $entityInstance,
            $this->getContext()->getRequest()
        );

        /** @var Category $entityInstance */
        $entityInstance->setCommodityType($this->getCategoryCommodityType());

        parent::persistEntity($entityManager, $entityInstance);
    }
    /**
     * @inheritdoc
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->setCategoryAttributes(
            $entityManager,
            $entityInstance,
            $this->getContext()->getRequest()
        );

        parent::updateEntity($entityManager, $entityInstance);
    }
    /**
     * Set category attributes data.
     *
     * @param   EntityManagerInterface  $entityManager  Entity manager.
     * @param   Category                $category       Category.
     * @param   Request                 $request        Request.
     *
     * @return  void
     */
    private function setCategoryAttributes(
        EntityManagerInterface  $entityManager,
        Category                $category,
        Request                 $request
    ): void {
        /** @var Attribute[] $attributesAll */
        $entityIndex                = 'Category';
        $attributesDataIndex        = self::CATEGORY_ATTRIBUTES_PROPERTY_NAME;
        $attributesPostData         = (array) ($request->request->all()[$entityIndex][$attributesDataIndex] ?? []);
        $allAttributes              = $this->getAllAttributesById($entityManager);
        $categoryExistAttributes    = [];

        foreach ($category->getCategoryAttributesParameters() as $attributeParameters) {
            $categoryExistAttributes[$attributeParameters->getId()] = $attributeParameters;
        }

        foreach ($attributesPostData as $attributeIncomeData) {
            $id                 = (int)     ($attributeIncomeData['id']                     ?? 0);
            $attributeId        = (int)     ($attributeIncomeData['attribute']              ?? 0);
            $isRequired         = (bool)    ($attributeIncomeData['required']               ?? false);
            $showOnList         = (bool)    ($attributeIncomeData['show_on_list']           ?? false);
            $listSortAlphabetic = (bool)    ($attributeIncomeData['list_sort_alphabetic']   ?? false);
            $sort               = (int)     ($attributeIncomeData['sort']                   ?? 0);
            $listValues         = (array)   ($attributeIncomeData['list_values']            ?? []);

            if (isset($categoryExistAttributes[$id])) {
                $attributeParameters = $categoryExistAttributes[$id];
                unset($categoryExistAttributes[$id]);
            } else {
                $attributeParameters = (new CategoryAttributeParameters())
                    ->setCategory($category);
            }

            $attributeParameters
                ->setAttribute($allAttributes[$attributeId] ?? null)
                ->setRequired($isRequired)
                ->setShowOnList($showOnList)
                ->setListSortAlphabetic($listSortAlphabetic)
                ->setSort($sort);

            if (
                $attributeParameters->getAttribute() &&
                in_array($attributeParameters->getAttribute()->getType(), [
                    Attribute::TYPE_LIST,
                    Attribute::TYPE_LIST_MULTIPLE,
                ])
            ) {
                $this->setAttributeParameterListValues($entityManager, $attributeParameters, $listValues);
            }

            $category->addCategoryAttributeParameters($attributeParameters);
            $entityManager->persist($attributeParameters);
        }

        foreach ($categoryExistAttributes as $categoryExistAttribute) {
            $category->removeCategoryAttributeParameters($categoryExistAttribute);
        }
    }
    /**
     * Get all attributes set.
     *
     * @param   EntityManagerInterface $entityManager   Entity manager.
     *
     * @return  Attribute[]                             Attributes set,
     *                                                  where key is attribute ID and
     *                                                  value is attribute entity.
     */
    private function getAllAttributesById(EntityManagerInterface $entityManager): array
    {
        $attributes = $entityManager
            ->getRepository(Attribute::class)
            ->findAll();
        $result     = [];

        foreach ($attributes as $attribute) {
            $result[$attribute->getId()] = $attribute;
        }

        return $result;
    }
    /**
     * Set attribute parameter list values.
     *
     * @param   EntityManagerInterface      $entityManager          Entity manager.
     * @param   CategoryAttributeParameters $attributeParameters    Attribute parameters.
     * @param   array                       $data                   List values income data.
     *
     * @return  void
     */
    private function setAttributeParameterListValues(
        EntityManagerInterface      $entityManager,
        CategoryAttributeParameters $attributeParameters,
        array                       $data
    ): void {
        $existListValues = [];

        foreach ($attributeParameters->getCategoryAttributeListValues() as $listValue) {
            $existListValues[$listValue->getId()] = $listValue;
        }

        foreach ($data as $listValue) {
            $listValueId            = (int)     ($listValue['id']       ?? 0);
            $listValueTitle         = (string)  ($listValue['value']    ?? '');
            $listValueTitleCleared  = trim($listValueTitle);

            if (strlen($listValueTitleCleared) === 0) {
                continue;
            }

            if (isset($existListValues[$listValueId])) {
                $categoryAttributeListValue = $existListValues[$listValueId];
                unset($existListValues[$listValueId]);
            } else {
                $categoryAttributeListValue = (new CategoryAttributeListValue())
                    ->setCategoryAttribute($attributeParameters);
            }

            $categoryAttributeListValue->setValue($listValueTitleCleared);

            $attributeParameters->addCategoryAttributeListValue($categoryAttributeListValue);
            $entityManager->persist($categoryAttributeListValue);
        }

        foreach ($existListValues as $existListValue) {
            $attributeParameters->removeCategoryAttributeListValue($existListValue);
        }
    }
    /**
     * Get commodity type for current category.
     *
     * @return string                       Commodity typ.
     */
    abstract protected function getCategoryCommodityType(): string;
}
