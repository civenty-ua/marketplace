<?php
namespace App\Traits;

use Doctrine\ORM\EntityRepository;
use Throwable;
use App\Entity\District;
use App\Entity\Locality;
use App\Entity\Region;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

trait LocationTrait {
    /**
     * @param FormBuilderInterface $formBuilder
     * @return FormBuilderInterface
     */
    private function addedLocationUserField(FormBuilderInterface $formBuilder, $options = null): FormBuilderInterface
    {
        //Добавляем область
        if ($formBuilder->has('region')) {
            $formBuilder->remove('region');
        }
        if ($formBuilder->has('district')) {
            $formBuilder->remove('district');
        }
        if ($formBuilder->has('locality') ) {
            $formBuilder->remove('locality');
        }
        $formBuilder->add(
            'region',
            EntityType::class,
            [
                'block_prefix' => 'profile_market_select_field',
                'placeholder' => 'form_registration.region_select',
                'required' => false,
                'label' => 'form_registration.region',
                'class' => Region::class,
                'query_builder' => function(EntityRepository $repository) {
                    return $repository
                        ->createQueryBuilder('region')
                        ->leftJoin('region.translations', 'regionTranslations')
                        ->where('regionTranslations.locale = :locale')
                        ->setParameter('locale',  $this->currentLocale)
                        ->addOrderBy('region.sort', 'ASC')
                        ->addOrderBy('regionTranslations.name', 'ASC');
                },
                'attr' => [
                    'data-entity' => 'region'
                ]
            ]
        );

        $this->districtAjaxFieldFormListener($formBuilder, $options);
        $this->localityAjaxFieldFormListener($formBuilder, $options);

        return $formBuilder;
    }

    private function districtAjaxFieldFormListener(FormBuilderInterface $builder, $options = null)
    {
        $localizationFormModifier = function (FormInterface $form, Region $region = null, $options = null ) {
            if (method_exists($this,'getDoctrine')) {
                $districtRegion = $this->getDoctrine();
            } else {
                $districtRegion = $options['entityManager'];
            }

            $districtInRegion = $districtRegion
                ->getRepository(District::class)
                ->findBy(["region" => $region],['name' => 'ASC']);

            $form
                ->add('district', EntityType::class, [
                    'block_prefix' => 'profile_market_select_field',
                    'choices'     => $districtInRegion,
                    'class' => District::class,
                    'label' => 'Район',
                    'required' => false,
                    'placeholder' => 'Виберіть район',
                    'attr' => [
                        'data-entity' => 'district'
                    ]
                ]);
        };
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($localizationFormModifier, $options) {
            try {
                /** @var User $data */
                $data = $event->getData();
                $localizationFormModifier($event->getForm(), $data->getRegion(), $options);
            } catch (Throwable $exception) {
                $localizationFormModifier($event->getForm(), null, $options);
            }
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($localizationFormModifier, $options) {
            $data      = $event->getData();
            $regionId = array_key_exists('region', $data) ? $data['region'] : null;
            if (method_exists($this,'getDoctrine')) {
                $doctrine = $this->getDoctrine();
            } else {
                $doctrine = $options['entityManager'];
            }
            $region = $doctrine->getRepository(Region::class)->find((Int)$regionId);
            $localizationFormModifier($event->getForm(), $region, $options);

        });
    }

    private function localityAjaxFieldFormListener(FormBuilderInterface $builder, $options = null)
    {
        $localizationFormModifier = function (FormInterface $form, District $district = null, $options = null) {
            if (method_exists($this,'getDoctrine')) {
                $localityDistrict = $this->getDoctrine();
            } else {
                $localityDistrict = $options['entityManager'];
            }

            $localityInDistrict = $localityDistrict->getRepository(Locality::class)
                ->findBy(["district" => $district],['name' => 'ASC']);


            $form
                ->add('locality', EntityType::class, [
                    'block_prefix' => 'profile_market_select_field',
                    'class' => Locality::class,
                    'choices'     => $localityInDistrict,
                    'label' => 'Населений пункт',
                    'required' => false,
                    'placeholder' => 'Виберіть населений пункт',
                    'attr' => [
                        'data-entity' => 'locality'
                    ]
                ]);
        };
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($localizationFormModifier, $options) {
            try {
                /** @var User $data */
                $data = $event->getData();
                $localizationFormModifier($event->getForm(), $data->getDistrict(), $options);
            } catch (Throwable $exception) {
                $localizationFormModifier($event->getForm(), null, $options);
            }
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($localizationFormModifier, $options) {
            $data      = $event->getData();
            $districtId = array_key_exists('district', $data) ? $data['district'] : null;
            if (method_exists($this,'getDoctrine')) {
                $doctrine = $this->getDoctrine();
            } else {
                $doctrine = $options['entityManager'];
            }
            $district = $doctrine->getRepository(District::class)->find((int)$districtId);
            $localizationFormModifier($event->getForm(), $district, $options);

        });
    }
}