<?php

namespace App\Form;

use App\Entity\CompanyMessages;
use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;
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

class OfferPriceForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name',
                TextType::class,
                [
                    'label' => false,
                    'translation_domain' => 'messages',
                    'attr' => [
                        'class' => 'form-text',
                        'placeholder' => 'contacts.name',
                    ],
                ])
            ->add('surname',
                TextType::class,
                [
                    'label' => false,
                    'translation_domain' => 'messages',
                    'attr' => [
                        'class' => 'form-text',
                        'placeholder' => 'contacts.surname',
                        'required' => false,
                    ],
                ])
            ->add('phone',
                TextType::class,
                [
                    'label' => false,
                    'required' => false,
                    'translation_domain' => 'messages',
                    'attr' => [
                        'class' => 'js-phone-mask form-text',
                        'placeholder' => 'form_registration.phone',
                        'required' => false,
                    ],
                ])
            ->add('email',
                EmailType::class,
                [
                    'label' => false,
                    'translation_domain' => 'messages',
                    'attr' => [
                        'class' => 'form-text',
                        'placeholder' => 'form_registration.email',
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Please enter your email',
                        ]),
                    ],
                ])
            ->add('message',
                TextareaType::class,
                [
                    'label' => false,
                    'required' => true,
                    'translation_domain' => 'messages',
                    'attr' => [
                        'class' => 'form-text',
                        'placeholder' => 'contacts.message',
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Please enter your email',
                        ]),
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
                Recaptcha3Type::class,
                [
                    'constraints' => new Recaptcha3([
                        'message' => 'form_registration.captcha',
                        'messageMissingValue' => 'form_registration.captcha_mis',
                    ]),
                    'action_name' => 'contacts',
                ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CompanyMessages::class,
        ]);
    }
}