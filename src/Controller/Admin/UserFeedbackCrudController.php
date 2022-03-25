<?php

namespace App\Controller\Admin;

use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use EasyCorp\Bundle\EasyAdminBundle\Config\{
    Action,
    Actions,
    Crud,
    Filters,
};
use EasyCorp\Bundle\EasyAdminBundle\Field\{
    ArrayField,
    BooleanField,
    ChoiceField,
    DateTimeField,
    NumberField,
    TextField,
};
use EasyCorp\Bundle\EasyAdminBundle\Filter\{
    DateTimeFilter,
    EntityFilter,
};
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\{
    BooleanFilterType,
};
use App\Admin\Filter\EntitySubfieldFilter;
use App\Service\ExportService;
use App\Entity\{
    Item,
    UserItemFeedback,
};

class UserFeedbackCrudController extends BaseCrudController
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public static function getEntityFqcn(): string
    {
        return UserItemFeedback::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX,
                Action::DETAIL,
                function (Action $action) {
                    return $action
                        ->setIcon('fas fa-eye')
                        ->setLabel(false)
                        ->setHtmlAttributes([
                            'placeholder' => 'Перегляд',
                        ]);
                })
            ->add(
                Crud::PAGE_INDEX,
                Action::new('export')
                    ->setLabel('user_feedback.export')
                    ->createAsGlobalAction()
                    ->linkToCrudAction('export')
                    ->addCssClass('btn btn-primary')
            )
            ->add(
                Crud::PAGE_DETAIL,
                Action::new('setIsActive', 'Змінити статус відповіді з типом відгук')
                    ->linkToCrudAction('setIsActive')
                    ->addCssClass('btn btn-primary')
            );
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('user')
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('user_feedback.user.name');
        yield NumberField::new('user.gender')
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('user_feedback.user.gender')
            ->formatValue(function($value, UserItemFeedback $feedback) {
                switch ($feedback->getUser()->getGender()) {
                    case 0:
                        return $this->translator->trans('admin.userFeedback.gender.male');
                    case 1:
                        return $this->translator->trans('admin.userFeedback.gender.female');
                    case null:
                    default:
                        return $this->translator->trans('admin.userFeedback.gender.none');
                }
            });
        yield TextField::new('user.region')
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('user_feedback.user.region');
        yield TextField::new('item')
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('user_feedback.parent_item.common');
        yield ChoiceField::new('item.typeItem')
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('user_feedback.parent_item_type')
            ->setChoices([
                $this->translator->trans('user_feedback.parent_item.article') => Item::ARTICLE,
                $this->translator->trans('user_feedback.parent_item.course') => Item::COURSE,
                $this->translator->trans('user_feedback.parent_item.webinar') => Item::WEBINAR,
                $this->translator->trans('user_feedback.parent_item.common') => Item::OTHER,
            ]);
        yield DateTimeField::new('created_at')
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('user_feedback.created_at');
        yield ArrayField::new('userFeedbackAnswers')
            ->setLabel('user_feedback.answers')
            ->hideOnIndex();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(
                EntityFilter::new('user')
                    ->setLabel('user_feedback.user.entity')
            )
            ->add(
                DateTimeFilter::new('createdAt')
                    ->setLabel('user_feedback.created_at')
            )
            ->add(
                EntityFilter::new('item')
                    ->setLabel('user_feedback.parent_item.common')
            )
            ->add(
                EntitySubfieldFilter::new(
                    'userGender',
                    'user',
                    'gender',
                    BooleanFilterType::class
                )
                    ->setLabel('user_feedback.user.gender')
                    ->setFormTypeOption('choices', [
                        $this->translator->trans('user_feedback.gender.male') => false,
                        $this->translator->trans('user_feedback.gender.female') => true,
                    ])
            );
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setEntityLabelInPlural('admin.dashboard.feedback_from_visitors.list_title')
            ->setEntityLabelInSingular('admin.dashboard.feedback_from_visitors.edit_button_title')
            ->setPageTitle(Crud::PAGE_NEW, 'admin.dashboard.feedback_from_visitors.new_page_title')
            ->setPageTitle(Crud::PAGE_EDIT, 'admin.dashboard.feedback_from_visitors.edit_page_title')
            ->setSearchFields([
                'user.name',
                'user.gender',
                'user.region.translations.name',
                'userFeedbackAnswers.answer',
            ])
            ->setFormThemes([
                '@A2lixTranslationForm/bootstrap_4_layout.html.twig',
                '@FOSCKEditor/Form/ckeditor_widget.html.twig',
                '@EasyAdmin/crud/form_theme.html.twig',
            ]);
    }

    /**
     * Export entities.
     *
     * @param ExportService $exportService Export service.
     *
     * @return void
     */
    public function export(ExportService $exportService): void
    {
        /** @var UserItemFeedback[] $items */
        $items = $this
            ->getDoctrine()
            ->getRepository(UserItemFeedback::class)
            ->findBy([]);
        $result = [];

        foreach ($items as $item) {
            $user = $item->getUser();
            $entity = $item->getItem();
            $answers = [];

            foreach ($item->getUserFeedbackAnswers() as $answer) {
                $fieldTitle = $answer->getFeedbackFormQuestion()->getTitle();
                $fieldAnswer = (string)$answer->getAnswer();
                $answers[] = "$fieldTitle: $fieldAnswer";
            }

            $result[] = [
                $this->translator->trans('user_feedback.user.name') => $user ? $user->getName() : null,
                $this->translator->trans('user_feedback.user.gender') => $user ? $user->getGender() : null,
                $this->translator->trans('user_feedback.user.region') => $user && $user->getRegion()
                    ? $user->getRegion()->getName()
                    : null,
                $this->translator->trans('user_feedback.parent_item.common') => $entity ? $entity->getTitle() : null,
                $this->translator->trans('user_feedback.created_at') => $item->getCreatedAt(),
                $this->translator->trans('user_feedback.answers') => implode("\n", $answers),
            ];
        }

        $exportService->export('user_feedback', $result);
    }

    public function setIsActive(Request $request)
    {
        $UserFeedbackId = (int)$request->query->get('entityId');
        $feedbackForm = $this->getDoctrine()->getManager()
            ->getRepository(UserItemFeedback::class)->findOneBy(['id' => $UserFeedbackId]);

        $wrongTypeOfAnswerCounter = 0;

        foreach ($feedbackForm->getUserFeedbackAnswers() as $answer) {
            if ($answer->getFeedbackFormQuestion()->getType() === 'review'
                && $answer->getFeedbackFormQuestion()->getRequired()) {
                if (is_null($answer->getIsActive()) || $answer->getIsActive() === false) {
                    $answer->setIsActive(true);
                    $this->getDoctrine()->getManager()->flush();
                    $this->addFlash('success', $this->translator->trans('admin.dashboard.feedback_from_visitors.status_action.active_msg'));
                } else {
                    $answer->setIsActive(false);
                    $this->getDoctrine()->getManager()->flush();
                    $this->addFlash('success', $this->translator->trans('admin.dashboard.feedback_from_visitors.status_action.not_active_msg'));
                }
            } else {
                $wrongTypeOfAnswerCounter++;
            }
        }

        if ($wrongTypeOfAnswerCounter == count($feedbackForm->getUserFeedbackAnswers()->toArray())) {
            $this->addFlash('success', $this->translator->trans('admin.dashboard.feedback_from_visitors.status_action.no_reviews'));
        }

        return $this->redirect($request->query->get('referrer'));
    }
}
