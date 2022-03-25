<?php

namespace App\Form\Feedback;

use Symfony\Component\Form\{
    AbstractType,
    FormBuilderInterface,
};
use Symfony\Component\Form\Extension\Core\Type\{
    CollectionType,
    SubmitType,
};
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\UserItemFeedback;

class UserFeedbackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('userFeedbackAnswers', CollectionType::class, [
                'label'         => false,
                'entry_type'    => UserFeedbackAnswerType::class,
                'entry_options' => [
                    'label' => false
                ],
                'attr'          => [
                    'class' => 'questions-bar',
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'feedback_form.save',
                'attr'  => [
                    'class' => 'form-button submit-button'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserItemFeedback::class,
        ]);
    }
}
