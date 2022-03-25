<?php
declare(strict_types = 1);

namespace App\Form\Profile\Market;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Validator\KitCommoditiesConstraint;
use App\Entity\{
    Market\Commodity,
    Market\CommodityKit,
};
/**
 * Profile, kit form.
 */
class KitFormType extends CommodityFormType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $commoditiesValidationParameters = [
            'min'                           => 2,
            'max'                           => 9,
            'requiredCreatorCommodity'      => true,
        ];

        $builder
            ->add('commodities', EntityType::class, [
                'block_prefix'      => 'profile_market_kit_commodities_field',
                'class'             => Commodity::class,
                'required'          => true,
                'error_bubbling'    => true,
                'multiple'          => true,
                'choice_label'      => 'title',
                'attr'              => $commoditiesValidationParameters,
                'constraints'       => [
                    new KitCommoditiesConstraint($commoditiesValidationParameters),
                ],
                'query_builder'     => function() {
                    return $this->entityManager
                        ->getRepository(Commodity::class)
                        ->listFilter($this->currentUser, 'id', [
                            'commodityType' => [
                                Commodity::TYPE_PRODUCT,
                                Commodity::TYPE_SERVICE,
                            ],
                        ]);
                },
            ])
            ->get('description')->setRequired(false);
    }
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => CommodityKit::class,
        ]);
    }
}
