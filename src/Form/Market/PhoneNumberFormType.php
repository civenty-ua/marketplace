<?php

namespace App\Form\Market;

use App\Entity\Market\Phone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\{CheckboxType,
    HiddenType,
    TelType,
    TextType,
    TextareaType,
    EmailType,
    SubmitType};
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class PhoneNumberFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('phone',
                TelType::class,
                [
                    'label' => false,
                    'attr' => [
                        'class' => 'js-phone-mask',
                        'placeholder' => '+38 (___) ___ __ __'
                    ],
                ])
            ->add('isTelegram', CheckboxType::class, [
                'label' => 'role.isTelegram',
                'row_attr' => [
                    'class' => 'checkbox-wrap'
                ],
                'required' => false
            ])
            ->add('isViber', CheckboxType::class, [
                'label' => 'role.isViber',
                'row_attr' => [
                    'class' => 'checkbox-wrap'
                ],
                'required' => false
            ])
            ->add('isWhatsApp', CheckboxType::class, [
                'label' => 'role.isWhatsApp',
                'row_attr' => [
                    'class' => 'checkbox-wrap'
                ],
                'required' => false
            ])

            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Phone::class,
            'cascade_validation' => true
        ]);
    }
}