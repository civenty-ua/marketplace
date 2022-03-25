<?php

namespace App\Form\Field\FeedbackForm;

use Symfony\Component\Form\{
    FormInterface,
    FormView,
};
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RateType extends ChoiceType
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
        return 'feedback_form_rate_field';
    }
}
