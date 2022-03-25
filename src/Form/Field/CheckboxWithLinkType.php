<?php

namespace App\Form\Field;

use Symfony\Component\Form\{
    FormInterface,
    FormView,
};
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;
/**
 * Checkbox field with link
 */
class CheckboxWithLinkType extends CheckboxType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('link', '');
    }
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars['link'] = $options['link'];
    }
    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'choice_with_link_field';
    }
}
