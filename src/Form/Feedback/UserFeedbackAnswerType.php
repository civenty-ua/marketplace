<?php

namespace App\Form\Feedback;

use Symfony\Component\Form\{
    AbstractType,
    FormBuilderInterface,
    FormEvent,
    FormEvents,
};
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\{
    FeedbackFormQuestion,
    UserItemFeedbackAnswer
};
use App\Form\Field\FeedbackForm\{
    StringType,
    NumberType,
    RateType,
};

class UserFeedbackAnswerType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            /** @var UserItemFeedbackAnswer|null $userFeedbackAnswer */
            $userFeedbackAnswer = $event->getData();
            $question           = $userFeedbackAnswer
                ? $userFeedbackAnswer->getFeedbackFormQuestion()
                : null;
            $form               = $event->getForm();

            if (!$question) {
                return;
            }

            $fieldName      = "answer:{$question->getId()}";
            $fieldOptions   = [
                'property_path' => 'answer',
                'label'         => $question->getTitle(),
                'required'      => $question->getRequired(),
                'tooltip'       => $question->getDescription(),
            ];

            switch ($question->getType()) {
                case FeedbackFormQuestion::TYPE_NUMBER:
                    $formTypeClass = NumberType::class;
                    break;
                case FeedbackFormQuestion::TYPE_RATE:
                    $choices = [];

                    foreach (range(
                        FeedbackFormQuestion::RATE_VALUE_MIN,
                        FeedbackFormQuestion::RATE_VALUE_MAX
                    ) as $value) {
                        $choices[$value] = $value;
                    }

                    $formTypeClass              = RateType::class;
                    $fieldOptions['choices']    = $choices;
                    break;
                case FeedbackFormQuestion::TYPE_STRING:
                default:
                    $formTypeClass = StringType::class;
            }

            $form->add($fieldName, $formTypeClass, $fieldOptions);
        });
    }
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserItemFeedbackAnswer::class,
        ]);
    }
}
