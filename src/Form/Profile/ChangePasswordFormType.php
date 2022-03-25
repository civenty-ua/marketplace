<?php

namespace App\Form\Profile;

use Symfony\Component\Form\{
    FormBuilderInterface,
    AbstractType,
};
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\OptionsResolver\OptionsResolver;
/**
 * Profile change password form.
 */
class ChangePasswordFormType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $passwordPlaceholder = '***********';

        $builder
            ->add('oldPassword', PasswordType::class, [
                'label' => 'profile.forms.change_password.old_password',
                'mapped' => false,
                'attr' => [
                    'class' => 'js-password-input form-text',
                    'placeholder' => $passwordPlaceholder,
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'profile.forms.change_password.new_password',
                'attr' => [
                    'class' => 'js-password-input form-text new-password',
                    'placeholder' => $passwordPlaceholder,
                ],
                'help' => 'form_registration.password_regex',
                'help_attr' => [ 'class' => 'personal-area-form-help-wrapper'],
                'translation_domain' => 'messages',
            ])
            ->add('passwordConfirm', PasswordType::class, [
                'label' => 'profile.forms.change_password.confirm_password',
                'attr' => [
                    'class' => 'js-password-input form-text',
                    'placeholder' => $passwordPlaceholder,
                    'display' => 'none'
                ],
                'translation_domain' => 'messages',
                'mapped' => false
            ]);

    }
    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'translation_domain' => 'messages'
        ]);
    }
}
