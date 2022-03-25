<?php

namespace App\Controller\Admin\Market;

use App\Entity\Market\RequestRole;
use App\Entity\User;
use App\Event\User\UserReceivedRoleEvent;
use App\Service\Notification\SystemNotificationSender;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\{
    BooleanField,
    ChoiceField,
    CollectionField,
    DateTimeField,
    IdField,
    TextField
};
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class RequestRoleCrudController extends MarketCrudController
{
    private $requestRoles;
    private $rolesChoices;
    private AdminUrlGenerator $urlGenerator;
    private TranslatorInterface $translator;
    private RequestStack $requestStack;
    private SystemNotificationSender $systemNotificationSender;
    protected EventDispatcherInterface $eventDispatcher;

    public static function getEntityFqcn(): string
    {
        return RequestRole::class;
    }

    public function __construct(
        AdminUrlGenerator        $urlGenerator,
        RequestStack             $requestStack,
        TranslatorInterface      $translator,
        EventDispatcherInterface $eventDispatcher,
        SystemNotificationSender $systemNotificationSender
    )
    {
        $rolesChoices = [];
        foreach (User::getAvailableRoles() as $role) {
            $rolesChoices["user.roles.$role"] = $role;
        }

        $requestRolesRaw = [
            'ROLE_SALESMAN' => 'salesman',
            'ROLE_WHOLESALE_BUYER' => 'wholesale-bayer',
            'ROLE_SERVICE_PROVIDER' => 'service-provider'
        ];

        $requestRoles = [];
        foreach ($rolesChoices as $key => $role) {
            if (isset($requestRolesRaw[$role])) {
                $requestRoles[$key] = $requestRolesRaw[$role];
            }
        }
        $this->requestRoles = $requestRoles;
        $this->rolesChoices = $rolesChoices;
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
        $this->requestStack = $requestStack;
        $this->eventDispatcher = $eventDispatcher;
        $this->systemNotificationSender = $systemNotificationSender;
    }

    public function configureFields(string $pageName): iterable
    {

        yield IdField::new('id')
            ->setLabel('ID')
            ->onlyOnIndex();
        yield TextField::new('user.name')
            ->setLabel('Користувач');

        yield TextField::new('user.userProperty.companyName')
            ->setLabel('Назва підприемства');

        yield TextField::new('user.userProperty.companyType')
            ->setLabel('Вид підприемства')
            ->onlyOnDetail();

        yield TextField::new('user.userProperty.legalCompanyType')
            ->setLabel('Тип власності')
            ->onlyOnDetail();

        yield TextField::new('user.email')
            ->setLabel('Email')
            ->onlyOnDetail();

        yield TextField::new('user.phone')
            ->setLabel('Телефон регістрації')
            ->onlyOnDetail();

        yield CollectionField::new('user.phones')
            ->setLabel('Телефони')
            ->onlyOnDetail();

        yield ChoiceField::new('user.gender')
            ->setLabel('form_registration.gender_label')
            ->setColumns('col-sm-6')
            ->autocomplete()
            ->setChoices([
                'form_registration.gender.men' => 0,
                'form_registration.gender.women' => 1,
            ])->onlyOnDetail();

        yield TextField::new('user.region')
            ->setLabel('Область')
            ->onlyOnDetail();

        yield TextField::new('user.district')
            ->setLabel('Район')
            ->onlyOnDetail();

        yield TextField::new('user.locality')
            ->setLabel('Населений пункт')
            ->onlyOnDetail();

        yield ChoiceField::new('user.roles', 'form_registration.roles')
            ->allowMultipleChoices()
            ->setColumns('col-sm-6')
            ->autocomplete()
            ->setChoices($this->rolesChoices);

        yield ChoiceField::new('role', 'Запит ролі')
            ->setColumns('col-sm-6')
            ->autocomplete()
            ->setChoices($this->requestRoles);
        yield BooleanField::new('isApproved')
            ->setLabel('Узгодження ролі');

        yield CollectionField::new('user.crops')
            ->setLabel('item.crops')
            ->setColumns('col-sm-6')
            ->onlyOnDetail();

        yield CollectionField::new('user.userProperty.address')
            ->setLabel('Адреса')
            ->setColumns('col-sm-6')
            ->onlyOnDetail();

        yield TextField::new('user.userProperty.facebookLink')
            ->setLabel('Facebook')
            ->onlyOnDetail();

        yield TextField::new('user.userProperty.instagramLink')
            ->setLabel('Instagram')
            ->onlyOnDetail();

        yield DateTimeField::new('createdAt')
            ->setLabel('Дата створення');
        yield DateTimeField::new('updatedAt')
            ->setLabel('Дата редагування');

        yield CollectionField::new('user.userProperty.userCertificates')
            ->setLabel('Сертифікати')
            ->setTemplatePath('admin/certificate/view.html.twig')
            ->setColumns('col-sm-6')
            ->onlyOnDetail();
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return $this->get(EntityRepository::class)->createQueryBuilder($searchDto, $entityDto, $fields, $filters)->andWhere('entity.isActive = true');
    }

    /**
     * @inheritdoc
     */
    protected function getMessagesDomain(): string
    {
        return 'market';
    }

    public function configureCrud(Crud $crud): Crud
    {

        return parent::configureCrud($crud)
            ->setDefaultSort(['updatedAt' => 'Desc'])
            ->setPageTitle(Crud::PAGE_INDEX, "Запити ролі")
            ->setPageTitle(Crud::PAGE_NEW, "Запит ролі")
            ->setPageTitle(Crud::PAGE_EDIT, "Запит ролі")
            ->setEntityLabelInPlural("Запити ролі")
            ->setEntityLabelInSingular("Запит ролі")
            ->setFormThemes([
                '@A2lixTranslationForm/bootstrap_4_layout.html.twig',
                '@EasyAdmin/crud/form_theme.html.twig',
            ])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        $checkRole = Action::new('approveRole', 'Погодити роль', 'fas fa-user-check')
            ->addCssClass('approveRequestRole btn')
            ->linkToCrudAction('approveRole');

        $deleteRequestRole = Action::new('deleteRequestRole', 'Відмовити в наданні ролі', 'far fa-trash-alt')
            ->linkToCrudAction('deleteRequestRole')
            ->addCssClass('deleteRequestRole btn')
            ->setHtmlAttributes([
                'href' => '#',
                'ajax-data-action' => $this->generateUrl('admin_delete_role_request'),
                'entityId' => $this->requestStack->getCurrentRequest()->query->get('entityId')
            ]);

        $actionsRow = parent::configureActions($actions);

        $customActions = $actionsRow->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('fas fa-eye')->setLabel(false);
            })
            ->add(Crud::PAGE_DETAIL, $checkRole)
            ->add(Crud::PAGE_DETAIL, $deleteRequestRole);


        $requestId = $this->requestStack->getCurrentRequest()->query->get('entityId');
        if ($requestId) {
            $requestRole = $this->getDoctrine()->getRepository(RequestRole::class)->findOneBy(['id' => $requestId]);
            if ($requestRole && $requestRole->getIsApproved() == true) {
                $actions->remove(Crud::PAGE_DETAIL, 'approveRole');
                $actions->remove(Crud::PAGE_DETAIL, 'deleteRequestRole');
            }
        }

        return $customActions;

    }

    /**
     * @param   AdminContext $context
     * @return  RedirectResponse
     */
    public function approveRole(AdminContext $context): RedirectResponse
    {
        /** @var RequestRole $requestRole */
        $requestRole = $context->getEntity()->getInstance();
        $requestRole->setIsApproved(true);
        $user = $requestRole->getUser();
        $userRoles = $user->getRoles();
        $newRolesArray = array_flip($this->requestRoles);
        $newRole = '';
        if (isset($newRolesArray[$requestRole->getRole()]) and isset($this->rolesChoices[$newRolesArray[$requestRole->getRole()]])) {
            $newRole = $this->rolesChoices[$newRolesArray[$requestRole->getRole()]];
        }
        $addedRole = false;
        if (!empty($newRole) and !in_array($newRole, $userRoles)) {
            $userRoles[] = $newRole;
            $user->setRoles($userRoles);
            $addedRole = true;
        }
        $em = $this->getDoctrine()->getManager();
        $requestRole->setUpdatedAt(new \DateTime());
        $em->persist($user);
        $em->persist($requestRole);
        $em->flush();

        $event = new UserReceivedRoleEvent(
            $user,
            User::getNormalRoleByCode($requestRole->getRole())
        );
        $this->eventDispatcher->dispatch($event);

        if ($addedRole) {
            $this->addFlash('success', 'Користувачу ' . $user->getName() . ' успішно додана роль ' . $this->translator->trans($newRolesArray[$requestRole->getRole()]));
            //todo use here notification sender service
            $this->systemNotificationSender->sendSingleNotification([
                'receiver' => $user,
                'message' => 'Вам узгоджена та додана роль ' . $this->translator->trans($newRolesArray[$requestRole->getRole()]),
                'title' => 'Підтвердження отримання ролі'
            ]);
        } else {
            $this->addFlash('info', 'У користувача ' . $user->getName() . ' роль ' . $this->translator->trans($newRolesArray[$requestRole->getRole()]) . ' вже додано раніше');
        }

        return $this->redirect(
            $this->urlGenerator
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl()
        );
    }


    /**
     * @Route("/admin/request-role/delete", name="admin_delete_role_request")
     */
    public function deleteRequestRole(Request $request): Response
    {
        $this->validateRequest($request);

        $requestRole = $this->getDoctrine()->getRepository(RequestRole::class)->find((int)$request->request->get('id'));
        if (!$requestRole) {
            return new JsonResponse(['message' => 'Request Role Not found.'], 404);
        }

        $user = $requestRole->getUser();
        $userName = $user->getName();
        $newRolesArray = array_flip($this->requestRoles);
        $roleName = $this->translator->trans($newRolesArray[$requestRole->getRole()]);
        $em = $this->getDoctrine()->getManager();
        $requestRole->setIsActive(false);
        $requestRole->setUpdatedAt(new \DateTime());
        $em->flush();

        $this->systemNotificationSender->sendSingleNotification([
            'receiver' => $user,
            'message' => 'Вам відмовлено у отриманні ролі ' . $roleName . '. ' . $request->request->get('message'),
            'title' => 'Відмова в отримання ролі'
        ]);

        $this->addFlash('error', 'Користувачу ' . $userName . ' відмовлено у наданні ролі ' . $roleName);

        return new JsonResponse([
            'url' => $this->urlGenerator
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->removeReferrer()
                ->setEntityId(null)
                ->generateUrl()
        ], 200);
    }

    private function validateRequest(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['message' => 'Bad Request!'], 400);
        }
        if ($this->isGranted(User::ROLE_ADMIN_MARKET) || !$this->isGranted(User::ROLE_SUPER_ADMIN)) {
            return new JsonResponse(['message' => 'Forbidden'], 403);
        }
    }
}
