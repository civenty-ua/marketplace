<?php
declare(strict_types = 1);

namespace App\Form\Profile\Market;

use Symfony\Component\Form\{
    AbstractType,
    FormBuilderInterface,
};
use Symfony\Component\Form\Extension\Core\Type\{
    TextType,
    TextareaType,
    SubmitType,
};
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Market\UserProperty;
/**
 * Profile, about me form.
 */
class AboutMeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('description', TextareaType::class, [
                'label'     => false,
                'required'  => false,
                'attr'      => [
                    'maxlength' => 1000,
                ],
            ])
            ->add('descriptionVideoLink', TextType::class, [
                'label'     => false,
                'required'  => false,
            ])
            ->add('submit', SubmitType::class);
    }
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => UserProperty::class,
        ]);
    }
}
