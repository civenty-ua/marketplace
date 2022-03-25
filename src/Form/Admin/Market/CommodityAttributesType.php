<?php

namespace App\Form\Admin\Market;

use Doctrine\ORM\{
    EntityManagerInterface,
    PersistentCollection,
};
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\{
    AbstractType,
    FormInterface,
    FormView,
};
use App\Entity\Market\{
    Attribute,
    CategoryAttributeParameters,
    Category,
    Commodity,
};
/**
 * Commodity attributes form, attributes values management bar.
 */
class CommodityAttributesType extends AbstractType
{
    private EntityManagerInterface $entityManager;
    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    /**
     * @inheritdoc
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        /**
         * @var PersistentCollection|ArrayCollection    $data
         * @var Commodity|null                          $commodity
         * @var Category[]                              $categoriesAll
         */
        $data           = $form->getData();
        $commodity      = $data instanceof PersistentCollection
            ? $data->getOwner()
            : null;
        $categoriesAll  = $this->entityManager
            ->getRepository(Category::class)
            ->findAll();

        $view->vars['attributesMapData']            = $this->getAttributesMapData($categoriesAll);
        $view->vars['attributesExistValuesData']    = $commodity
            ? $this->getAttributesExistValuesData($commodity)
            : [];
    }
    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'allow_extra_fields' => true,
        ]);
    }
    /**
     * @inheritdoc
     */
    public function getBlockPrefix(): string
    {
        return 'commodity_attributes_values';
    }
    /**
     * Get attributes map data.
     *
     * @param   Category[] $categories      Categories.
     *
     * @return  array                       Data.
     */
    private function getAttributesMapData(array $categories): array
    {
        $result = [];

        foreach ($categories as $category) {
            $result[$category->getId()] = [];

            foreach ($category->getCategoryAttributesParameters() as $attributeParameters) {
                $result[$category->getId()][] = [
                    'id'            => $attributeParameters->getAttribute()->getId(),
                    'title'         => $attributeParameters->getAttribute()->getTitle(),
                    'type'          => $attributeParameters->getAttribute()->getType(),
                    'required'      => $attributeParameters->getRequired(),
                    'listValues'    => $this->getAttributeListValues($attributeParameters),
                ];
            }
        }

        return $result;
    }
    /**
     * Get attribute list values.
     *
     * @param   CategoryAttributeParameters $attributeParameters    Attribute parameters.
     *
     * @return  array                                               List values.
     */
    private function getAttributeListValues(CategoryAttributeParameters $attributeParameters): array
    {
        switch ($attributeParameters->getAttribute()->getType()) {
            case Attribute::TYPE_LIST:
            case Attribute::TYPE_LIST_MULTIPLE:
                $result = [];

                foreach ($attributeParameters->getCategoryAttributeListValues() as $listValue) {
                    $result[$listValue->getId()] = $listValue->getValue();
                }

                return $result;
            case Attribute::TYPE_DICTIONARY:
                return $attributeParameters
                    ->getAttribute()
                    ->loadDictionaryList($this->entityManager);
            default:
                return [];
        }
    }
    /**
     * Get attributes exist values data.
     *
     * @param   Commodity $commodity        Commodity.
     *
     * @return  array                       Data.
     */
    private function getAttributesExistValuesData(Commodity $commodity): array
    {
        $result = [];

        foreach ($commodity->getCommodityAttributesValues() as $attributeValue) {
            $result[$attributeValue->getAttribute()->getId()] = $attributeValue->getValue();
        }

        return $result;
    }
}
