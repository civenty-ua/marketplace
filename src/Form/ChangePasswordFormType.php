<?php

namespace App\Form;

use App\Validator\PasswordConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChangePasswordFormType extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class,
                [
                    'translation_domain' => 'messages',
                    'type' => PasswordType::class,
                    'first_options' => [
                        'label' => 'form_reset.new_password',
                        'attr' => [
                            'class' => 'form-text new-password',
                        ],
                        'row_attr' => [
                            'class' => 'form-block',
                        ],
                        'help' => 'form_registration.password_regex',
                        'help_attr' => [ 'class' => 'personal-area-form-help-wrapper'],
                        'constraints' => [
                            new NotBlank([
                                'message' => 'Please enter a password',
                            ]),
                            new PasswordConstraint(),
                        ],
                    ],
                    'second_options' => [
                        'label' => 'form_reset.repeat_password',
                        'attr' => [
                            'class' => 'form-text',
                        ],
                        'row_attr' => [
                            'class' => 'form-block',
                        ],
                    ],
                    'invalid_message' => $this->translator->trans('form_reset.invalid_message'),
                    // Instead of being set onto the object directly,
                    // this is read and encoded in the controller
                    'mapped' => false,
                ]
            )
            ->add('save', SubmitType::class,
                [
                    'label' => 'form_reset.send_password',
                    'attr' => [
                        'class' => 'form-button centeredBlock',
                    ],
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
