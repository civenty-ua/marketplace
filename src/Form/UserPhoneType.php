<?php

namespace App\Form;

use App\Entity\Market\Phone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\{CheckboxType, TelType, TextType};
use Symfony\Component\OptionsResolver\OptionsResolver;


class UserPhoneType extends AbstractType
{

    // TODO: fields "required" parameter does not work. Find out why! Now "attr" parameter is used instead.
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('phone',
                TelType::class,
                [
                    'label' => 'form_registration.phone',
                    'attr' => [
                        'class' => 'js-phone-mask form-text js-user-phone-input',
                        'placeholder' => '+38 (___) ___ __ __'
                    ],
                    'required' => true,
                    'translation_domain' => 'messages',
                ])
            ->add('isMain', CheckboxType::class, [
                'label' => 'Основний'
            ])
            ->add('isTelegram', CheckboxType::class, [
                'label' => 'Telegram'
            ])
            ->add('isViber', CheckboxType::class, [
                'label' => 'Viber'
            ])
            ->add('isWhatsApp', CheckboxType::class, [
                'label' => 'WatsApp',
            ])
            ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Phone::class,
        ]);
    }
}
