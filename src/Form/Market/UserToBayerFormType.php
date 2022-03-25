<?php

namespace App\Form\Market;

use App\Entity\CompanyMessages;
use App\Entity\Crop;
use App\Entity\District;
use App\Entity\Locality;
use App\Entity\Region;
use App\Repository\DistrictRepository;
use App\Repository\LocalityRepository;
use App\Repository\RegionRepository;
use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Form\Extension\Core\Type\{
    TextType,
    TextareaType,
    EmailType,
    SubmitType,
};
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserToBayerFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('title',
                TextType::class,
                [
                    'label' => false,
                    'attr' => [
                        'class' => 'form-text',
                        'placeholder' => 'form_user.title',
                    ],
                ])
            ->add('type',
                TextType::class,
                [
                    'label' => false,
                    'attr' => [
                        'class' => 'form-text',
                        'placeholder' => 'form_user.type',
                    ],
                ])
            ->add('name',
                TextType::class,
                [
                    'label' => false,
                    'attr' => [
                        'class' => 'form-text',
                        'placeholder' => 'form_user.name',
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Please enter your name',
                        ]),
                    ],
                ])
            ->add('phone',
                TextType::class,
                [
                    'label' => false,
                    'attr' => [
                        'class' => 'js-phone-mask form-text js-user-phone-input',
                        'placeholder' => '+38 (___) ___ __ __'
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Please enter your phone',
                        ]),
                    ],
                ])
            ->add('email',
                EmailType::class,
                [
                    'label' => false,
                    'attr' => [
                        'class' => 'form-text',
                        'placeholder' => 'form_user.email',
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Please enter your email',
                        ]),
                    ],
                ])
            ->add('region',
                EntityType::class,
                [
                    'class' => Region::class,
                    'choice_label' => 'name',
                    'label' => 'form_user.region',
                    'attr' => [
                        'class' => 'select-input height-50',
                    ],
                    'translation_domain' => 'messages',
//                    'query_builder' => function (RegionRepository $er) {
//                        return $er->createQueryBuilder('r')
//                            ->addOrderBy('r.name', 'ASC');
//                    }
                ])
            ->add('district',
                EntityType::class,
                [
                    'class' => District::class,
                    'choice_label' => 'name',
                    'label' => 'form_user.district',
                    'attr' => [
                        'class' => 'select-input height-50',
                    ],
                    'translation_domain' => 'messages',
                    'query_builder' => function (DistrictRepository $er) {
                        return $er->createQueryBuilder('d')
                            ->addOrderBy('d.name', 'ASC');
                    }
                ])
            ->add('locality',
                EntityType::class,
                [
                    'class' => Locality::class,
                    'choice_label' => 'name',
                    'label' => 'form_user.locality',
                    'attr' => [
                        'class' => 'select-input height-50',
                    ],
                    'translation_domain' => 'messages',
                    'query_builder' => function (LocalityRepository $er) {
                        return $er->createQueryBuilder('d')
                            ->addOrderBy('d.name', 'ASC');
                    }
                ])
            ->add('crops', EntityType::class, [
                'label' => 'profile.forms.profile_edit.user_crops',
                'choice_label' => 'name',
                'multiple' => true,
                'mapped' => true,
                'required' => false,
                'class' => Crop::class,
                'attr' => [
                    'class' => 'js-select2 select-input height-50',
                ]
            ])
            ->add('facebookLink',
                TextType::class,
                [
                    'label' => false,
                    'attr' => [
                        'class' => 'form-text',
                        'placeholder' => 'form_user.facebookLink',
                    ],
                ])
            ->add('instagramLink',
                TextType::class,
                [
                    'label' => false,
                    'attr' => [
                        'class' => 'form-text',
                        'placeholder' => 'form_user.instagramLink',
                    ],
                ])
            ->add('address',
                TextType::class,
                [
                    'label' => false,
                    'attr' => [
                        'class' => 'form-text',
                        'placeholder' => 'form_user.address',
                    ],
                ])

            ->add('save',
                SubmitType::class,
                [
                    'label' => 'contacts.send',
                    'attr' => [
                        'class' => 'form-button',
                    ],
                ])
            ->add('captcha',
                Recaptcha3Type::class, [
                'constraints' => new Recaptcha3([
                    'message' => 'form_registration.captcha',
                    'messageMissingValue' => 'form_registration.captcha_mis'
                ]),
                'action_name' => 'contacts',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'translation_domain' => 'messages'
        ]);
    }
}