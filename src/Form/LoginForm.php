<?php

namespace App\Form;

use App\Form\Field\PrettyCheckbox;
use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\{CheckboxType,
    PasswordType,
    TextType,
    TextareaType,
    EmailType,
    SubmitType
};
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('_username', EmailType::class, [
                'label' => 'form_registration.email',
                'data' => $options['lastUsername'],
                'attr' => [
                    'class' => 'form-text',
                ],
            ])
            ->add('_password', PasswordType::class, [
                'label' => 'form_registration.plain_password',
                'attr' => [
                    'class' => 'form-text',
                ],
            ])
            ->add('_remember_me', PrettyCheckbox::class, [
                'required' => false,
            ])
            ->add('save',
                SubmitType::class,
                [
                    'label' => 'form_registration.login',
                    'attr' => [
                        'class' => 'form-button',
                    ],
                ]);
            $builder->add('captcha', Recaptcha3Type::class, [
                'constraints' => new Recaptcha3([
                    'message' => 'form_registration.captcha',
                    'messageMissingValue' => 'form_registration.captcha_mis'
                ]),
                'action_name' => 'login'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            //default csrf parameters defined in Symfony codes. without this configuratio csrf check will fail
            'csrf_field_name' => '_csrf_token',
            'csrf_token_id' => 'authenticate',

            'lastUsername' => null
        ]);
    }
    public function getBlockPrefix()
    {
        return '';
    }
}