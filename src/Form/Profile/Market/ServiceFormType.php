<?php
declare(strict_types = 1);

namespace App\Form\Profile\Market;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;
use App\Entity\Market\{
    Category,
    Commodity,
    CommodityService,
};
/**
 * Profile, service form.
 */
class ServiceFormType extends CommodityWithCategoryFormType
{
    protected const CATEGORY_COMMODITIES_TYPES = [
        Commodity::TYPE_SERVICE
    ];
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('imageFile', VichImageType::class, [
            'block_prefix'      => 'profile_market_image_field',
            'required'          => true,
            'error_bubbling'    => true,
            'attr'              => [
                'maxSize'           => 5 * 1024 * 1024,
                'allowMimeTypes'    => [
                    'image/jpeg',
                    'image/jpg',
                    'image/png',
                    'image/pjpeg',
                    'image/webp',
                    'application/pdf',
                    'application/x-pdf',
                ],
            ],
        ]);
    }
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => CommodityService::class,
        ]);
    }
}
