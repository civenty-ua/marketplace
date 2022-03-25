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
    Category,
    CategoryAttributeParameters,
};
/**
 * Category edit form, attributes management bar.
 */
class CategoryAttributesType extends AbstractType
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
         * @var Category|null                           $category
         * @var CategoryAttributeParameters[]           $attributesParameters
         */
        $data                       = $form->getData();
        $category                   = $data instanceof PersistentCollection
            ? $data->getOwner()
            : null;
        $attributesParameters       = $category
            ? $category->getCategoryAttributesParameters()->toArray()
            : [];
        $attributesParametersById   = [];

        foreach ($attributesParameters as $attributeParameter) {
            $attributesParametersById[$attributeParameter->getAttribute()->getId()] = $attributeParameter;
        }

        $view->vars['attributesAll']    = $this->entityManager
            ->getRepository(Attribute::class)
            ->findBy([], [
                'title' => 'asc',
            ]);
        $view->vars['attributesExist']  = $attributesParametersById;
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
        return 'category_attributes';
    }
}
