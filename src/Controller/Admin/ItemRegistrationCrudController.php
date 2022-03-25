<?php

namespace App\Controller\Admin;

use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    Session\SessionInterface,
};
use EasyCorp\Bundle\EasyAdminBundle\Config\{
    Action,
    Actions,
    Filters,
    Crud,
};
use EasyCorp\Bundle\EasyAdminBundle\Field\{
    AssociationField,
    TextField,
    DateTimeField,
};
use App\Service\MailSender\Provider\MailSenderProviderInterface;
use App\Service\ExportService;
use App\Entity\{Item, ItemRegistration, User, UserItemFeedback};
/**
 * Class ItemRegistrationCrudController
 */
abstract class ItemRegistrationCrudController extends BaseCrudController
{
    protected UrlGeneratorInterface       $urlGenerator;
    protected TranslatorInterface         $translator;
    protected MailSenderProviderInterface $mailSender;
    protected SessionInterface            $session;

    public function __construct(
        UrlGeneratorInterface       $urlGenerator,
        TranslatorInterface         $translator,
        MailSenderProviderInterface $mailSender,
        SessionInterface            $session
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->translator   = $translator;
        $this->mailSender   = $mailSender;
        $this->session      = $session;
    }
    /**
     * @inheritDoc
     */
    public static function getEntityFqcn(): string
    {
        return ItemRegistration::class;
    }
    /**
     * @inheritDoc
     */
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('userId')
            ->add('itemId')
            ->add('createdAt');
    }
    /**
     * @inheritDoc
     */
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ->remove(Crud::PAGE_INDEX,Action::EDIT)
            ->add(
                Crud::PAGE_INDEX,
                Action::new('sendFeedbackForm', false)
                    ->linkToCrudAction('sendFeedbackForm')
                    ->setIcon('fa fa-paper-plane')
                    ->setLabel('admin.itemRegistration.actions.sendFeedbackForm')
            )
            ->add(
                Crud::PAGE_INDEX,
                Action::new('bulkSendFeedbackForm', false)
                    ->linkToCrudAction('bulkSendFeedbackForm')
                    ->setIcon('fa fa-paper-plane')
                    ->setLabel('admin.itemRegistration.actions.sendFeedbackForms')
                    ->createAsBatchAction()
            )
            ->add(
                Crud::PAGE_INDEX,
                Action::new('itemRegistrationExport', 'Export')
                    ->createAsGlobalAction()
                    ->linkToCrudAction('export')
                    ->addCssClass('btn btn-primary')
            );
    }
    /**
     * @inheritDoc
     */
    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('userId')
            ->setLabel('admin.itemRegistration.user');
        yield TextField::new('userId.region')
            ->setLabel('admin.itemRegistration.userRegion')
            ->formatValue(function($value, ItemRegistration $registration) {
                $region = $registration->getUserId()->getRegion();

                return $region ? $region->getName() : null;
            });
        yield AssociationField::new('itemId')
            ->setLabel('admin.itemRegistration.item');
        yield DateTimeField::new('createdAt')
            ->setLabel('admin.itemRegistration.createdDate');
        yield TextField::new('userLeftFeedback')
            ->setLabel('admin.itemRegistration.userFeedback.title')
            ->setVirtual(true)
            ->formatValue(function($value, ItemRegistration $registration) {
                $userFeedback = $this->findUserFeedback($registration);

                return $userFeedback
                    ? $this->translator->trans('admin.itemRegistration.userFeedback.exist')
                    : $this->translator->trans('admin.itemRegistration.userFeedback.notExist');
            });
        yield TextField::new('itemType')->setLabel('Тип')->onlyOnIndex();
    }
    /**
     * @inheritDoc
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPageTitle(Crud::PAGE_INDEX, 'admin.itemRegistration.titles.index')
            ->setEntityLabelInPlural('admin.itemRegistration.titles.plural')
            ->setEntityLabelInSingular('admin.itemRegistration.titles.singular')
            ->showEntityActionsInlined();
    }
    /**
     * @param ExportService $exportService
     * @param Request $request
     * @return void
     */
    public function export(ExportService $exportService, Request $request): void
    {
        $query = $this->getDoctrine()
            ->getRepository('App:ItemRegistration')
            ->createQueryBuilder('ir')
            ->setMaxResults(1000)
            ->getQuery();

        $itemRegistrationList = $query->getResult();

        $exportData = [];

        /** @var ItemRegistration $itemRegistration */
        foreach ($itemRegistrationList as $itemRegistration) {
            $exportData[] = [
                'ID' => $itemRegistration->getId(),
                'Користувач' => $itemRegistration->getUserId()->getName(),
                'Події' => $itemRegistration->getItemId()->getTitle(),
                'Дата' => $itemRegistration->getCreatedAt(),
            ];
        }

        $exportService->export('users', $exportData);
    }

    abstract protected function getExportData(iterable $itemRegistrationList):array;

    /**
     * Send feedback form.
     */
    public function sendFeedbackForm(Request $request): Response
    {
        /** @var ItemRegistration|null $registration */
        $backUrl        = $request->query->get('referrer');
        $registrationId = (int) $request->query->get('entityId');
        $registration   = $this
            ->getDoctrine()
            ->getRepository(ItemRegistration::class)
            ->findOneBy([
                'id' => $registrationId,
            ]);

        if (!$registration->getItemId()->getFeedbackForm()) {
            $error = $this->translator->trans('admin.itemRegistration.errors.itemHasNoFeedbackForm');
            $this->session->getFlashBag()->add('info', $error);

            return $this->redirect($backUrl);
        }

        $this->sendFeedbackFormEmail($registration->getItemId(), $registration->getUserId());
        return $this->redirect($backUrl);
    }

    public function bulkSendFeedbackForm(Request $request): Response
    {
        /** @var ItemRegistration|null $registration */
        $backUrl        = $request->query->get('referrer');
        $registrationIds = $request->request->get('batchActionEntityIds');
        $registrations   = $this
            ->getDoctrine()
            ->getRepository(ItemRegistration::class)
            ->findBy([
                'id' => $registrationIds,
            ]);
        $itemsWithoutFeedbackForms = [];
        foreach ($registrations as $registration) {
            if (!$registration->getItemId()->getFeedbackForm()) {
                $itemsWithoutFeedbackForms[] = $registration->getItemId()->getTitle();
            }
        }
        if (!empty($itemsWithoutFeedbackForms)) {
            $str = implode(', ', $itemsWithoutFeedbackForms);
            $error = $this->translator->trans('admin.itemRegistration.errors.itemsHasNoFeedbackForm');
            $this->session->getFlashBag()->add('info', $error . $str);
            return $this->redirect($backUrl);
        }
        foreach ($registrations as $registration) {
            $this->sendFeedbackFormEmail($registration->getItemId(), $registration->getUserId());
        }

        return $this->redirect($backUrl);
    }
    /**
     * Find user feedback.
     *
     * @param   ItemRegistration $registration  Item registration.
     *
     * @return  UserFeedback|null               User feedback, if any.
     */

    protected function findUserFeedback(ItemRegistration $registration): ?UserItemFeedback
    {
        return $this
            ->getDoctrine()
            ->getRepository(UserItemFeedback::class)
            ->findOneBy([
                'user'  => $registration->getUserId(),
                'item'  => $registration->getItemId(),
            ]);
    }
    /**
     * Send feedback form email.
     *
     * @param   Item    $item               Item.
     * @param   User    $user               User.
     *
     * @return  void
     */
    private function sendFeedbackFormEmail(Item $item, User $user): void
    {
        switch ($item->getTypeItem()) {
            case Item::COURSE:
                $itemType1 = $this->translator->trans('email.item_feedback.types.course');
                $itemType2 = $this->translator->trans('email.item_feedback.types2.course');
                break;
            case Item::WEBINAR:
                $itemType1 = $this->translator->trans('email.item_feedback.types.webinar');
                $itemType2 = $this->translator->trans('email.item_feedback.types2.webinar');
                break;
            case Item::OCCURRENCE:
                $itemType1 = $this->translator->trans('email.item_feedback.types.occurrence');
                $itemType2 = $this->translator->trans('email.item_feedback.types2.occurrence');
                break;
            default:
                $itemType1 = $this->translator->trans('email.item_feedback.types.other');
                $itemType2 = $this->translator->trans('email.item_feedback.types2.other');
        }

        $title = str_replace(
            ['%type%', '%name%'],
            [$itemType1, $item->getTitle()],
            $this->translator->trans('email.item_feedback.title')
        );
        $body = str_replace(
            ['%type%', '%name%'],
            [$itemType2, $item->getTitle()],
            $this->translator->trans('email.item_feedback.body')
        );
        $linkUrl = $this->urlGenerator->generate(
            'item_feedback_form',
            [
                'slug' => $item->getSlug(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $link = str_replace(
            '%link%',
            $linkUrl,
            $this->translator->trans('email.item_feedback.link')
        );

        $this->mailSender->send(
            $user->getEmail(),
            $title,
            'email/item-feedback-form.html.twig',
            [
                'body'  => $body,
                'link'  => $link,
            ]
        );
    }
}
