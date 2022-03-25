<?php

namespace App\Form\Market;

use App\Entity\CompanyMessages;
use App\Entity\Crop;
use App\Entity\District;
use App\Entity\Locality;
use App\Entity\Region;
use App\Entity\User;
use App\Repository\DistrictRepository;
use App\Repository\LocalityRepository;
use App\Repository\RegionRepository;
use App\Traits\LocationTrait;
use App\Validator\PhoneMessengersConstraint;
use Doctrine\ORM\EntityRepository;
use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Form\Extension\Core\Type\{CollectionType,
    HiddenType,
    TextType,
    TextareaType,
    EmailType,
    SubmitType
};
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserCreateRoleFormType extends AbstractType
{
    use LocationTrait;

    protected ?string $currentLocale;

    public function __construct(RequestStack $requestStack)
    {
        $this->currentLocale = $requestStack->getCurrentRequest()->getLocale();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('userProperty', UserPropertyFormType::class, [
                'error_bubbling' => false,
                'block_prefix' => 'profile_market_role_collection',
                'entityManager' => $options['entityManager'],
                'row_attr' => [
                    'style' => 'display:none'
                ],
                'role' => $options['role'],
            ])
            ->add('name',
                TextType::class,
                [
                    'error_bubbling' => false,
                    'block_prefix' => 'profile_market_text_field',
                    'label' => 'Ім\'я, прізвище, по батькові',
                    'attr' => [
                        'readonly' => 'readonly',
                        'class' => 'form-text',
                        'placeholder' => 'form_user.name',
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Please enter your name',
                        ]),
                    ],
                ])
            ->add('crops', EntityType::class, [
                'block_prefix' => 'profile_market_select_field',
                'label' => 'profile.forms.profile_edit.user_crops',
                'choice_label' => 'name',
                'error_bubbling' => false,
                'multiple' => true,
                'mapped' => true,
                'required' => true,
                'class' => Crop::class,
                'query_builder' => function(EntityRepository $repository) {
                    return $repository
                            ->createQueryBuilder('crop')
                            ->leftJoin('crop.translations', 'cropTranslations')
                            ->where('cropTranslations.locale = :locale')
                            ->setParameter('locale', $this->currentLocale)
                            ->orderBy('cropTranslations.name', 'ASC');
                } ,
                'attr' => [
                    'class' => 'js-select2 select-input height-50',
                ]
            ])
            ->add('phones', CollectionType::class, [
                'block_prefix' => 'profile_market_role_phone',
                'label' => false,
                'error_bubbling' => false,
                'entry_type' => PhoneNumberFormType::class,
                'by_reference' => false,
                'prototype' => true,
                'allow_add' => true,
                'allow_delete' => true,
                'constraints' => [
                    new PhoneMessengersConstraint()
                ]
            ])
            ->add('submit', SubmitType::class, [
                'attr' => ['hidden' => 'hidden']
            ]);

        $this->addedLocationUserField($builder, $options);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'cascade_validation' => true,
            'entityManager' => null,
            'role' => '',
            'allow_add' => true,
        ]);
    }
}
