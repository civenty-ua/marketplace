<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Entity\Market\UserProperty;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\{
    KernelEvents,
    Event\RequestEvent,
};
use Twig\Environment;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use App\Security\LoginFormAuthenticator;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class RequestSubscriber implements EventSubscriberInterface
{
    use TargetPathTrait;

    private const FWName = 'main';

    private SessionInterface $session;

    private TokenStorageInterface $tokenStorage;

    private RouterInterface $router;
    private EntityManagerInterface $entityManager;
    private GuardAuthenticatorHandler $authenticatorHandler;
    private LoginFormAuthenticator $loginAuthenticator;
    private Environment $twigEngine;

    public function __construct(
        EntityManagerInterface         $entityManager,
        SessionInterface $session,
        TokenStorageInterface $tokenStorage,
        RouterInterface $router,
        GuardAuthenticatorHandler $authenticatorHandler,
        LoginFormAuthenticator $loginAuthenticator,
        Environment $twigEngine
    )
    {

        $this->entityManager = $entityManager;
        $this->twigEngine = $twigEngine;
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->session = $session;
        $this->authenticatorHandler = $authenticatorHandler;
        $this->loginAuthenticator = $loginAuthenticator;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (
            !$event->isMasterRequest()
            || $request->isXmlHttpRequest()
            || 'login' === $request->attributes->get('_route')
            || 'liip_imagine_filter' === $request->attributes->get('_route')
        ) {
            //return;
        } else {
            $targetPath = $this->getTargetPath($this->session, $this::FWName);

            if(empty($targetPath) || strcasecmp($request->headers->get('referer'), $request->getUri()) !== 0 )
            {
                $this->saveTargetPath($this->session, $this::FWName, $request->getUri());
            }
        }
        if(str_contains($request->getRequestUri(),'/marketplace'))
        {
            $this->checkWellcomeModal($event);
        }

    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest']
        ];
    }

    /**
     * Get current user, if exists.
     *
     * @return User|null
     */
    private function getCurrentUser(): ?User
    {
        $user =  $this->tokenStorage->getToken()
            ? $this->tokenStorage->getToken()->getUser()
            : null;

        return $user instanceof User ? $user : null;
    }

    private function checkWellcomeModal(RequestEvent $event)
    {
        if($user = $this->getCurrentUser())
        {
            if(!$user->getUserProperty()->getIsShowedModal())
            {
                $user->getUserProperty()->setIsShowedModal(true);
                $this->entityManager->flush();

                $this->authenticatorHandler->authenticateUserAndHandleSuccess(
                    $user,
                    $event->getRequest(),
                    $this->loginAuthenticator,
                    'main'
                );

                $this->session->getFlashBag()->add(
                    'html',
                    $this->twigEngine->render('modal/welcome-modal.html.twig', ['roles' => $user->getRoles()])
                );
            }
        }
    }
}