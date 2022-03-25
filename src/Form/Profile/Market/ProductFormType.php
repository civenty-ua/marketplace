<?php
declare(strict_types = 1);

namespace App\Form\Profile\Market;

use Doctrine\ORM\{
    EntityRepository,
    QueryBuilder,
};
use Symfony\Component\Form\{
    FormBuilderInterface,
    FormEvent,
    FormEvents,
};
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\{
    CheckboxType,
    ChoiceType,
};
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Vich\UploaderBundle\Form\Type\VichImageType;
use App\Entity\{
    User,
    District,
    Locality,
    Region,
    Market\Commodity,
    Market\CommodityProduct,
};
/**
 * Profile, product form.
 */
class ProductFormType extends CommodityWithCategoryFormType
{
    protected const CATEGORY_COMMODITIES_TYPES = [
        Commodity::TYPE_PRODUCT
    ];
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $typesChoices = [];
        foreach ($this->productAllowedTypesProvider->get($this->currentUser) as $type) {
            $label = $this->translator->trans("market.profile.productForm.types.$type");
            $typesChoices[$label] = $type;
        }

        $builder
            ->add('imageFile', VichImageType::class, [
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
            ])
            ->add('type', ChoiceType::class, [
                'block_prefix'      => 'profile_market_select_field',
                'required'          => true,
                'error_bubbling'    => true,
                'choices'           => $typesChoices,
            ])
            ->add('region', EntityType::class, [
                'block_prefix'      => 'profile_market_select_field',
                'class'             => Region::class,
                'required'          => false,
                'error_bubbling'    => true,
                'choice_label'      => 'name',
                'query_builder'     => function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('region')
                        ->leftJoin('region.translations', 'regionTranslations')
                        ->where('regionTranslations.locale = :locale')
                        ->setParameter('locale', $this->currentLocale)
                        ->addOrderBy('region.sort', 'ASC')
                        ->addOrderBy('regionTranslations.name', 'ASC');
                },
            ])
            ->add('district', EntityType::class, [
                'block_prefix'      => 'profile_market_select_field',
                'class'             => District::class,
                'required'          => false,
                'error_bubbling'    => true,
                'choice_label'      => 'name',
                'query_builder'     => function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('district')
                        ->orderBy('district.name', 'ASC');
                },
            ])
            ->add('locality', EntityType::class, [
                'block_prefix'      => 'profile_market_select_field',
                'class'             => Locality::class,
                'required'          => false,
                'error_bubbling'    => true,
                'choice_label'      => 'name',
                'query_builder'     => function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('locality')
                        ->orderBy('locality.name', 'ASC');
                },
            ])
            ->add('isOrganic', CheckboxType::class, [
                'block_prefix'      => 'profile_market_checkbox_field',
                'required'          => false,
                'error_bubbling'    => true,
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                /**
                 * @var CommodityProduct|null   $product
                 * @var QueryBuilder            $districtQueryBuilder
                 * @var QueryBuilder            $localityQueryBuilder
                 */
                $product                = $event->getData();
                $districtQueryBuilder   = $event->getForm()
                    ->get('district')
                    ->getConfig()
                    ->getOptions()['query_builder'];
                $localityQueryBuilder   = $event->getForm()
                    ->get('locality')
                    ->getConfig()
                    ->getOptions()['query_builder'];

                $this->controlProductLocalities($product);

                $districtQueryBuilder
                    ->where("{$districtQueryBuilder->getAllAliases()[0]}.region = :region")
                    ->setParameters([
                        'region' => $product->getRegion(),
                    ]);
                $localityQueryBuilder
                    ->where("{$localityQueryBuilder->getAllAliases()[0]}.district = :district")
                    ->setParameters([
                        'district' => $product->getDistrict(),
                    ]);
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
                /**
                 * @var QueryBuilder    $districtQueryBuilder
                 * @var QueryBuilder    $localityQueryBuilder
                 */
                $districtQueryBuilder   = $event->getForm()
                    ->get('district')
                    ->getConfig()
                    ->getOptions()['query_builder'];
                $localityQueryBuilder   = $event->getForm()
                    ->get('locality')
                    ->getConfig()
                    ->getOptions()['query_builder'];

                $districtQueryBuilder
                    ->where("{$districtQueryBuilder->getAllAliases()[0]}.id IS NOT NULL")
                    ->setParameters([]);
                $localityQueryBuilder
                    ->where("{$localityQueryBuilder->getAllAliases()[0]}.id IS NOT NULL")
                    ->setParameters([]);
            });
    }
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => CommodityProduct::class,
        ]);
    }
    /**
     * Run product localities fields values control.
     *
     * @param   CommodityProduct $product   Product.
     *
     * @return  void
     */
    private function controlProductLocalities(CommodityProduct $product): void
    {
        if (!$product->getRegion()) {
            $product->setDistrict(null);
            $product->setLocality(null);
            return;
        }

        if (
            !$product->getDistrict() ||
            $product->getDistrict()->getRegion() !== $product->getRegion()
        ) {
            $product->setDistrict(null);
            $product->setLocality(null);
            return;
        }

        if ($product->getLocality() && (
            $product->getLocality()->getDistrict()              !== $product->getDistrict() ||
            $product->getLocality()->getDistrict()->getRegion() !== $product->getRegion()
            )
        ) {
            $product->setLocality(null);
        }
    }
}
