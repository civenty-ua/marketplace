<?php
declare(strict_types = 1);

namespace App\Form\Market;

use App\Entity\UserToUserReview;
use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\{
    TextareaType,
    SubmitType};
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Profile, about me form.
 */
class ProfileUserToUserReviewFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('reviewText', TextareaType::class, [
                'label'     => false,
                'required'  => true,
                'attr'      => [
                    'maxlength' => 1000,
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Будь ласка, введіть текст відгуку',
                    ])
                ],
            ]) ->add('captcha',
                Recaptcha3Type::class, [
                    'constraints' => new Recaptcha3([
                        'message' => 'form_registration.captcha',
                        'messageMissingValue' => 'form_registration.captcha_mis'
                    ]),
                    'action_name' => 'userToUserReview',
                ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserToUserReview::class,
        ]);
    }
}