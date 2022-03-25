<?php
declare(strict_types=1);

namespace App\Controller\Profile;

use DateTime;
use App\Event\User\UserLostRoleEvent;
use App\Service\Notification\SystemNotificationSender;
use App\Entity\TextBlocks;
use App\Entity\Market\RequestRole;
use App\Service\Notification\NotificationDataService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Form\Market\UserCreateRoleFormType;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Entity\User;
use App\Form\Profile\Market\AboutMeFormType;
use App\Entity\Market\UserProperty;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Controller\AuthRequiredControllerInterface;
/**
 * Class ProfileMarketController
 * @package App\Controller\Profile
 */
class ProfileMarketController extends AbstractController implements AuthRequiredControllerInterface
{
    private TranslatorInterface $translator;
    private EventDispatcherInterface $eventDispatcher;
    private NotificationDataService $notificationDataService;
    private SystemNotificationSender $systemNotificationSender;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        TranslatorInterface      $translator,
        EventDispatcherInterface $eventDispatcher,
        NotificationDataService  $notificationDataService,
        SystemNotificationSender $systemNotificationSender,
        TokenStorageInterface $tokenStorage
    )
    {
        $this->translator = $translator;
        $this->eventDispatcher = $eventDispatcher;
        $this->notificationDataService = $notificationDataService;
        $this->systemNotificationSender = $systemNotificationSender;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @Route("/profile/market/about-me", name="market_profile_about_me")
     */
    public function aboutMe(Request $request): Response
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        $userProperty = $currentUser->getUserProperty() ?? (new UserProperty())->setUser($currentUser);
        $form = $this->createForm(AboutMeFormType::class, $userProperty);
        $notificationCount = $this->notificationDataService->getUserNotificationCounters($currentUser);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($userProperty);
            $entityManager->flush();

            return $this->redirectToRoute('market_profile_about_me');
        }

        return $this->render('profile/market/aboutMe.html.twig', [
            'form' => $form->createView(),
            'allUserNotificationCount' => $notificationCount['allUserNotificationCount'],
            'unreadUserNotificationCount' => $notificationCount['unreadUserNotificationCount'],
        ]);
    }


    /**
     * @Route("/profile/market/roles", name="market_profile_roles")
     */
    public function myRoles(): Response
    {
        $textBlock = $this->getDoctrine()->getRepository(TextBlocks::class)->findAll();
        $notificationCount = $this->notificationDataService->getUserNotificationCounters($this->getUser());
        return $this->render('profile/market/roles.html.twig', [
            'user' => $this->getUser(),
            'allUserNotificationCount' => $notificationCount['allUserNotificationCount'],
            'unreadUserNotificationCount' => $notificationCount['unreadUserNotificationCount'],
            'infoBlocks' => $textBlock
        ]);
    }

    /**
     * @Route("/profile/market/role/delete/{role}", name="profile_form_role_delete")
     * @Security("!user.getIsBanned()")
     */
    public function formRoleDelete(
        $role,
        Request $request,
        ?UserInterface $user
    ): Response {
        /** @var User|null $user */

        $requestRoles = [
            'salesman' => ['role' => 'ROLE_SALESMAN', 'name' => $this->translator->trans('user.roles.ROLE_SALESMAN')],
            'wholesale-bayer' => ['role' => 'ROLE_WHOLESALE_BUYER', 'name' => $this->translator->trans('user.roles.ROLE_WHOLESALE_BUYER')],
            'service-provider' => ['role' => 'ROLE_SERVICE_PROVIDER', 'name' => $this->translator->trans('user.roles.ROLE_SERVICE_PROVIDER')]
        ];

        $requestRole = $this->getDoctrine()->getManager()->getRepository(RequestRole::class)
            ->findOneBy(['user' => $user, 'role' => $role, 'isActive' => true]); //todo add isActive true

        if ($requestRole) {
            $requestRole->setIsActive(false);
            $requestRole->setUpdatedAt(new DateTime());
        }
        $userRoles = $user->getRoles();

        if (isset($requestRoles[$role]) and !empty($requestRoles[$role]) and in_array($requestRoles[$role]['role'], $userRoles)) {
            $newRoles = array_diff($userRoles, [$requestRoles[$role]['role']]);
            $user->setRoles($newRoles);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $event = new UserLostRoleEvent(
            $user,
            User::getNormalRoleByCode($role)
        );
        $this->eventDispatcher->dispatch($event);

        $this->systemNotificationSender->sendSingleNotification([
            'title' => 'Відмова від ролі ' . $requestRoles[$role]['name'],
            'message' => 'Ви успішно відмовилися від ролі ' . $requestRoles[$role]['name'],
            'receiver' => $user
        ]);

        $request->getSession()->invalidate();
        $this->tokenStorage->setToken();

        return $this->redirectToRoute('market_profile_roles');
    }

    /**
     * @Route("/profile/market/role/create/{role}", name="profile_form_role_create")
     * @Security("!user.getIsBanned()")
     */
    public function formRoleCreate(Request $request, $role, ?UserInterface $user): Response
    {
        /** @var User|null $user */
        $form = $this->createForm(UserCreateRoleFormType::class, $user, [
            'entityManager' => $this->getDoctrine(),
            'role' => $role,
        ]);
        $notificationCount = $this->notificationDataService->getUserNotificationCounters($this->getUser());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $requestRole = $this->getDoctrine()->getManager()->getRepository(RequestRole::class)->getCurrentRequestRole($user, $role);
            if (is_null($requestRole)) {
                $requestRole = new RequestRole();
            }

            $requestRole->setRole($role);
            $requestRole->setUser($user);
            $requestRole->setUpdatedAt(new DateTime());
            $requestRole->setIsActive(true);

            $em = $this->getDoctrine()->getManager();
            $em->persist($requestRole);
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('market_profile_roles');
        }

        return $this->render('profile/market/roles/form_role_create.html.twig', [
            'user' => $this->getUser(),
            'form' => $form->createView(),
            'role' => User::getNameRolesByCode(trim(strtolower($role))),
            'allUserNotificationCount' => $notificationCount['allUserNotificationCount'],
            'unreadUserNotificationCount' => $notificationCount['unreadUserNotificationCount'],

        ]);
    }
}
