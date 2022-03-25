<?php

namespace App\Controller\Admin\Market;

use App\Admin\Field\VichImageField;
use App\Entity\Market\Notification\SystemMessage;
use App\Entity\Market\UserCertificate;
use App\Event\Notification\NotificationEvent;
use App\Repository\UserRepository;
use App\Service\Notification\SystemNotificationSender;
use Doctrine\ORM\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserCertificateCrudController extends MarketCrudController
{

    private AdminUrlGenerator $urlGenerator;
    private TranslatorInterface $translator;
    private RequestStack $requestStack;
    protected EventDispatcherInterface $eventDispatcher;
    protected SystemNotificationSender $systemNotificationSender;

    public static function getEntityFqcn(): string
    {
        return UserCertificate::class;
    }

    public function __construct(
        AdminUrlGenerator   $urlGenerator,
        RequestStack        $requestStack,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        SystemNotificationSender $systemNotificationSender
    )
    {
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
        $this->requestStack = $requestStack;
        $this->eventDispatcher = $eventDispatcher;
        $this->systemNotificationSender = $systemNotificationSender;
    }

    public function configureFields(string $pageName): iterable
    {

        yield IdField::new('id')
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('admin.market.certificate.id')
            ->onlyOnIndex();

        yield AssociationField::new('userProperty')
            ->setColumns('col-sm-6 col-md-4')
            ->setFormTypeOptions([
                'query_builder' => function(EntityRepository $repository) {
                    return $repository
                        ->createQueryBuilder('userProperty')
                        ->leftJoin('userProperty.user', 'userPropertyUser')
                        ->orderBy('userPropertyUser.name', 'ASC');
                },
            ])
            ->setLabel('admin.market.certificate.userProperty');

        yield TextField::new('name')
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('admin.market.certificate.title');

        yield BooleanField::new('isEcology')
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('admin.market.certificate.isEcology');

        $file = VichImageField::new('file')
            ->setColumns('col-sm-6 col-md-4 hide-delete-btn')
            ->setLabel('admin.market.certificate.file')
            ->onlyOnForms();
        if (Crud::PAGE_NEW === $pageName) {
            $file->setRequired(true);
        } else {
            $file->setRequired(false);
        }
        yield $file;

        yield DateField::new('createdAt')
            ->setColumns('col-sm-6 col-md-4')
            ->hideWhenCreating()
            ->setLabel('admin.market.certificate.createdAt')
            ->onlyOnForms();

        yield BooleanField::new('approved')
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('admin.market.certificate.approved');

        yield TextField::new('originalName')
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('admin.market.certificate.originalName')
            ->onlyOnForms();

        yield IntegerField::new('fileSize')
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('admin.market.certificate.fileSize')
            ->setFormTypeOption('disabled','disabled')
            ->onlyOnForms();

        yield TextField::new('mimeType')
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('admin.market.certificate.MIMETypeOfFile')
            ->setFormTypeOption('disabled','disabled')
            ->onlyOnForms();
    }

    public function configureActions(Actions $actions): Actions
    {
        $checkStatus = Action::new('approveCertificate', 'admin.market.certificate.approveAction', 'fas fa-user-check')
            ->addCssClass('btn')
            ->linkToCrudAction('approveCertificate');

        $deleteCertificate = Action::new('deleteCertificate', 'admin.market.certificate.deleteAction', 'far fa-trash-alt')
            ->addCssClass('btn')
            ->linkToCrudAction('deleteCertificate');

        $actionsRow = parent::configureActions($actions);

        $customActions = $actionsRow->remove(Crud::PAGE_INDEX, Action::NEW)
            //->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->add(Crud::PAGE_EDIT, $checkStatus)
            ->add(Crud::PAGE_EDIT, $deleteCertificate);

        $requestId = $this->requestStack->getCurrentRequest()->query->get('entityId');

        if ($requestId) {
            $requestStatus = $this->getDoctrine()->getRepository(UserCertificate::class)->findOneBy(['id' => $requestId]);
            if ($requestStatus && ($requestStatus->getApproved() === true || !$requestStatus->getIsEcology())) {
                $actions->remove(Crud::PAGE_EDIT,'approveCertificate');
                $actions->remove(Crud::PAGE_EDIT,'deleteCertificate');
            }
        }

        return $customActions;
    }

    /**
     * @param AdminContext $context
     * @return RedirectResponse
     */
    public function approveCertificate(AdminContext $context): RedirectResponse
    {
        /** @var UserCertificate $userCertificate */
        $userCertificate = $context->getEntity()->getInstance();

        $approved = $userCertificate->setApproved(true);

        $user = $userCertificate->getUserProperty()->getUser();

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->persist($approved);
        $em->flush();


        $this->addFlash('success', 'Користувачу ' . $user->getName() . ' успішно узгоджено сертифікат "' . $userCertificate->getName() . '"');
        $this->systemNotificationSender->sendSingleNotification([
            'receiver' => $user,
            'message' => 'Вам узгоджено сертифікат "' . $userCertificate->getName() . '"',
            'title' => 'Узгодження еко-сертифіката',
        ]);


        return $this->redirect(
            $this->urlGenerator
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl()
        );
    }

    /**
     * @param AdminContext $context
     * @return RedirectResponse
     */
    public function deleteCertificate(AdminContext $context): RedirectResponse
    {
        /** @var UserCertificate $userCertificate */
        $userCertificate = $context->getEntity()->getInstance();

        $request = $context->getRequest();

        $user = $userCertificate->getUserProperty()->getUser();

        $userName = $user->getName();

        $certificateName = $userCertificate->getName();

        $em = $this->getDoctrine()->getManager();
        $em->remove($userCertificate);
        $em->flush();

        $this->systemNotificationSender->sendSingleNotification([
            'receiver' => $user,
            'message' => 'Вам відмовлено в узгодженні сертифікату ' . $certificateName . '. ' . $request->query->get('message'),
            'title' => 'Сертифікат не узгоджено'
        ]);

        $this->addFlash('error', 'Користувачу ' . $userName . ' відмовлено в узгодженні сертифікату "' . $certificateName . '"');

        return $this->redirect(
            $this->urlGenerator
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->removeReferrer()
                ->setEntityId(null)
                ->generateUrl()

        );
    }


    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['id']);
    }

    /**
     * @inheritdoc
     */
    protected function getMessagesDomain(): string
    {
        return 'certificate';
    }
}
