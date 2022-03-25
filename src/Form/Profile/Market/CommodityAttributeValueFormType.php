<?php
declare(strict_types = 1);

namespace App\Form\Profile\Market;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\{
    AbstractType,
    FormBuilderInterface,
    FormEvent,
    FormEvents,
};
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\{
    ChoiceType,
    NumberType,
    TextType,
};
use App\Entity\Market\{
    Attribute,
    Category,
    CategoryAttributeParameters,
    CommodityAttributeValue,
};
use function array_merge;

/**
 * Profile, create product.
 */
class CommodityAttributeValueFormType extends AbstractType
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
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            /** @var CommodityAttributeValue|null $attributeValue */
            $attributeValue         = $event->getData();
            $attribute              = $attributeValue->getAttribute();
            $attributesParameters   = $this->findAttributeParameters($attributeValue);

            if (!$attributesParameters) {
                return;
            }

            switch ($attribute->getType()) {
                case Attribute::TYPE_LIST:
                case Attribute::TYPE_LIST_MULTIPLE:
                    $inputType          = ChoiceType::class;
                    $inputParameters    = [
                        'block_prefix'      => 'profile_market_select_field',
                        'multiple'          => $attribute->getType() === Attribute::TYPE_LIST_MULTIPLE,
                        'choices'           => [],
                    ];

                    foreach ($attributesParameters->getCategoryAttributeListValues() as $listValue) {
                        $inputParameters['choices'][$listValue->getValue()] = $listValue->getId();
                    }
                    break;
                case Attribute::TYPE_INT:
                    $inputType          = NumberType::class;
                    $inputParameters    = [
                        'block_prefix'      => 'profile_market_number_field',
                        'attr'              => [
                            'min' => 0,
                        ],
                    ];
                    break;
                case Attribute::TYPE_DICTIONARY:
                    $inputType          = ChoiceType::class;
                    $inputParameters    = [
                        'block_prefix'      => 'profile_market_select_field',
                        'choices'           => [],
                    ];

                    foreach ($attribute->loadDictionaryList($this->entityManager) as $value => $title) {
                        $inputParameters['choices'][$title] = $value;
                    }
                    break;
                default:
                    $inputType          = TextType::class;
                    $inputParameters    = [
                        'block_prefix' => 'profile_market_text_field',
                        'attr'          => [
                            'maxlength' => 255,
                        ],
                    ];
            }

            $event->getForm()->add('value', $inputType, array_merge($inputParameters, [
                'label'     => $attribute->getTitle(),
                'required'  => $attributesParameters->getRequired(),
                'attr'      => array_merge($inputParameters['attr'] ?? [], [
                    'hint'      => $attribute->getDescription(),
                ]),
            ]));
        });
    }
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class'            => CommodityAttributeValue::class,
            'allow_extra_fields'    => true,
        ]);
    }
    /**
     * Try to find attribute parameters.
     *
     * @param   CommodityAttributeValue $attributeValue Attribute value.
     *
     * @return  CategoryAttributeParameters|null        Attribute parameters, if any.
     */
    private function findAttributeParameters(
        CommodityAttributeValue $attributeValue
    ): ?CategoryAttributeParameters {
        /** @var Category $category */
        $category = $attributeValue->getCommodity()->getCategory();

        if ($category) {
            foreach ($category->getCategoryAttributesParameters() as $attributesParameters) {
                if ($attributesParameters->getAttribute() === $attributeValue->getAttribute()) {
                    return $attributesParameters;
                }
            }
        }

        return null;
    }
}
