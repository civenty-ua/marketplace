<?php
declare(strict_types = 1);

namespace App\Form\Profile\Market;

use Doctrine\ORM\{
    EntityRepository,
    QueryBuilder,
};
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\{
    FormEvent,
    FormEvents,
    FormInterface,
    FormBuilderInterface,
};
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Entity\Market\{
    Category,
    Commodity,
    CommodityProduct,
    CommodityService,
    CommodityAttributeValue,
};
/**
 * Profile, commodity abstract form (with category).
 */
abstract class CommodityWithCategoryFormType extends CommodityFormType
{
    protected const CATEGORY_COMMODITIES_TYPES = [];
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                /** @var CommodityProduct|CommodityService|null $commodity */
                $commodity = $event->getData();

                $this->normalizeCommodityAttributesParameters($commodity);
                $this->addCategoryFields($event->getForm(), $commodity->getCategory());
            })
            ->addEventListener(FormEvents::POST_SET_DATA, function(FormEvent $event) {
                /** @var Category|null $category */
                $category = $event->getData()->getCategory();

                if ($category && $category->getParent()) {
                    $event->getForm()->get('parentCategory')->setData($category->getParent());
                } elseif (
                    $category &&
                    !$category->getParent() &&
                    count($category->getChildren()) > 0
                ) {
                    $event->getForm()->get('parentCategory')->setData($category);
                    $event->getForm()->get('category')->setData(null);
                }
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
                /** @var CommodityProduct|CommodityService|null $commodity */
                $commodity  = $event->getForm()->getData();
                $categoryId = (int) ($event->getData()['category'] ?? 0);
                $category   = $this->entityManager->getRepository(Category::class)->find($categoryId);

                $commodity->setCategory($category);
                $this->normalizeCommodityAttributesParameters($commodity);
                $this->addCategoryFields($event->getForm(), $category);
                $this->submitCommodityAttributesValues(
                    $commodity,
                    $event->getForm(),
                    $event->getData()
                );
            });
    }
    /**
     * Process setting category to form.
     *
     * @param   CommodityProduct|CommodityService $commodity    Commodity.
     *
     * @return  void
     */
    private function normalizeCommodityAttributesParameters(Commodity $commodity): void
    {
        $existValues = [];

        foreach ($commodity->getCommodityAttributesValues() as $attributeValue) {
            $existValues[$attributeValue->getAttribute()->getId()] = $attributeValue;
            $commodity->removeCommodityAttributeValue($attributeValue);
        }

        if ($commodity->getCategory()) {
            foreach ($commodity->getCategory()->getCategoryAttributesParameters() as $attributeParameters) {
                $attributeValue = $existValues[$attributeParameters->getAttribute()->getId()] ??
                    (new CommodityAttributeValue())
                        ->setCommodity($commodity)
                        ->setAttribute($attributeParameters->getAttribute());
                $commodity->addCommodityAttributeValue($attributeValue);
            }
        }
    }
    /**
     * Add category fields.
     *
     * @param   FormInterface   $form       Form.
     * @param   Category|null   $category   Category.
     *
     * @return  void
     */
    private function addCategoryFields(FormInterface $form, ?Category $category): void
    {
        $fieldBasicParameters = [
            'block_prefix'      => 'profile_market_select_field',
            'class'             => Category::class,
            'required'          => true,
            'error_bubbling'    => true,
            'choice_label'      => 'title',
        ];

        if (!$category || (
            !$category->getParent() &&
            count($category->getChildren()) === 0
        )) {
            $form->add('category', EntityType::class, array_merge($fieldBasicParameters, [
                'query_builder' => function (EntityRepository $entityRepository) {
                    return $this->buildCategoryQueryBuilder($entityRepository, null);
                },
            ]));
        } else {
            $form
                ->add('parentCategory', EntityType::class, array_merge($fieldBasicParameters, [
                    'mapped'        => false,
                    'query_builder' => function (EntityRepository $entityRepository) {
                        return $this->buildCategoryQueryBuilder($entityRepository, null);
                    },
                ]))
                ->add('category', EntityType::class, array_merge($fieldBasicParameters, [
                    'query_builder' => function (EntityRepository $entityRepository) use($category) {
                        return $this->buildCategoryQueryBuilder(
                            $entityRepository,
                            $category->getParent() ? $category->getParent() : $category
                        );
                    },
                ]));
        }

        $form->add('commodityAttributesValues', CollectionType::class, [
            'block_prefix'          => 'profile_market_commodity_attributes_field',
            'entry_type'            => CommodityAttributeValueFormType::class,
            'allow_extra_fields'    => true,
            'entry_options'         => [],
        ]);
    }
    /**
     * Build category selector query builder.
     *
     * @param   EntityRepository    $entityRepository   Repository.
     * @param   Category|null       $parentCategory     Parent category.
     *
     * @return  QueryBuilder                            Query builder.
     */
    private function buildCategoryQueryBuilder(
        EntityRepository    $entityRepository,
        ?Category           $parentCategory
    ): QueryBuilder {
        $alias          = 'category';
        $queryBuilder   = $entityRepository->createQueryBuilder($alias);

        $queryBuilder
            ->andWhere(
                $queryBuilder->expr()->in("$alias.commodityType", ':types')
            )
            ->setParameter('types', static::CATEGORY_COMMODITIES_TYPES)
            ->orderBy("$alias.title", 'ASC');

        if ($parentCategory) {
            $queryBuilder
                ->andWhere(
                    $queryBuilder->expr()->eq("$alias.parent", ':parent')
                )
                ->setParameter('parent', $parentCategory);
        } else {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->isNull("$alias.parent")
            );
        }

        return $queryBuilder;
    }
    /**
     * Submit commodity attributes.
     *
     * @param   Commodity       $commodity      Commodity.
     * @param   FormInterface   $form           Form.
     * @param   array           $data           Submit data.
     *
     * @return  void
     */
    private function submitCommodityAttributesValues(
        Commodity       $commodity,
        FormInterface   $form,
        array           $data
    ): void {
        /** @var CommodityAttributeValue[] $commodityAttributes */
        $incomeValuesRaw        = (array) ($data['commodityAttributesValues'] ?? []);
        ksort($incomeValuesRaw);
        $incomeValues           = array_values($incomeValuesRaw);
        $commodityAttributes    = array_values($commodity->getCommodityAttributesValues()->toArray());

        foreach ($commodityAttributes as $index => $attributeValue) {
            $attributeValue->setValue($incomeValues[$index]['value'] ?? null);
        }

        foreach ($form->get('commodityAttributesValues')->all() as $child) {
            foreach ($child->all() as $subChild) {
                $name       = $subChild->getName();
                $class      = $subChild->getConfig()->getType()->getInnerType();
                $options    = $subChild->getConfig()->getOptions();

                $child
                    ->remove($name)
                    ->add($name, get_class($class), array_merge($options, [
                        'mapped' => false,
                    ]));
            }
        }
    }
}
