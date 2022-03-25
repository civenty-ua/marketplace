<?php

namespace App\Form\Field;

use Symfony\Component\Form\{
    FormInterface,
    FormView,
};
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;
/**
 * PrettyCheckbox
 */
class PrettyCheckbox extends CheckboxType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
    }
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

    }
    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'pretty_checkbox';
    }
}
