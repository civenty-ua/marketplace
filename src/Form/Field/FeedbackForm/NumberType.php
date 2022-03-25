<?php

namespace App\Form\Field\FeedbackForm;

use Symfony\Component\Form\{
    FormInterface,
    FormView,
};
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NumberType extends IntegerType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('tooltip', '');
        $resolver->setAllowedTypes('tooltip', ['null', 'string']);
    }
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars['tooltip'] = $options['tooltip'];
    }
    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'feedback_form_number_field';
    }
}
