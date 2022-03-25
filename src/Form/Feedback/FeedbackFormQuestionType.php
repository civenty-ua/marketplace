<?php

namespace App\Form\Feedback;

use App\Entity\FeedbackFormQuestion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\{
    TextType,
    TextareaType,
    IntegerType,
    ChoiceType,
    CheckboxType
};
use Symfony\Component\OptionsResolver\OptionsResolver;
use A2lix\TranslationFormBundle\Form\Type\TranslationsType;

class FeedbackFormQuestionType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    // TODO: fields "required" parameter does not work. Find out why! Now "attr" parameter is used instead.
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $questionTypesList = [];

        foreach (FeedbackFormQuestion::getAllowedTypes() as $type) {
            $questionTypesList["feedback_form.question.types.$type"] = $type;
        }

        $builder
            ->add('translations', TranslationsType::class, [
                'default_locale' => '%locale%',
                'label' => 'Переклади',
                'fields' => [
                    'title' => [
                        'field_type' => TextType::class,
                        'label' => 'feedback_form.question.title',
                        'attr' => [
                            'required' => 'required',
                        ],
                        'help' => $this->translator->trans('admin.dashboard.feedback_form.title_help')
                    ],
                    'description' => [
                        'field_type' => TextareaType::class,
                        'label' => 'feedback_form.question.description',
                    ],
                ],
                'excluded_fields' => [
                    'parameters',
                    'keywords',
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'feedback_form.question.type',
                'choices' => $questionTypesList,
            ])
            ->add('required', CheckboxType::class, [
                'label' => 'feedback_form.question.required',
            ])
            ->add('sort', IntegerType::class, [
                'label' => 'feedback_form.question.sort',
                'attr' => [
                    'required' => 'required',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FeedbackFormQuestion::class,
        ]);
    }
}
