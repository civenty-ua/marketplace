<?php

namespace App\Form;

use Symfony\Component\Form\{
    AbstractType,
    FormBuilderInterface,
};
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use App\Entity\Comment;
use Symfony\Contracts\Translation\TranslatorInterface;

class CommentType extends AbstractType
{
    public $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('message', CKEditorType::class, [
                'label' => false,
                'required' => true,
                'input_sync' => true,
                'config' => [
                    'toolbar' => 'basic',
                    'editorplaceholder' => $this->translate()
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'comment.new.save',
                'attr' => [
                    'class' => 'form-button'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }

    public function translate()
    {
        return $this->translator->trans('comment.new.messagePlaceholder');
    }
}
