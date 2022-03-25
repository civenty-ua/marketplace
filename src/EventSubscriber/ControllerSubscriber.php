<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\HttpKernel\{
    KernelEvents,
    Event\ControllerEvent,
};
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Controller\AuthRequiredControllerInterface;
use App\Entity\User;
/**
 * Controllers events subscriber.
 */
class ControllerSubscriber implements EventSubscriberInterface
{
    private TokenStorageInterface $tokenStorage;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        TokenStorageInterface   $tokenStorage,
        UrlGeneratorInterface   $urlGenerator
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->urlGenerator = $urlGenerator;
    }
    /**
     * On controller run event handler.
     *
     * @param   ControllerEvent $event  Event.
     *
     * @return  void
     */
    public function onControllerRequest(ControllerEvent $event): void
    {
        $controllerData = $event->getController();
        $controller     = is_array($controllerData) ? $controllerData[0] : null;

        if (!$controller) {
            return;
        }

        $this->handleAuthRequiredController($event, $controller);
    }
    /**
     * Process authorization required controller run.
     *
     * @param   ControllerEvent $event      Controller run event.
     * @param   object          $controller Controller.
     *
     * @return  void
     */
    private function handleAuthRequiredController(ControllerEvent $event, object $controller): void
    {
        $currentUser = $this->tokenStorage->getToken()
            ? $this->tokenStorage->getToken()->getUser()
            : null;

        if (
            $controller instanceof AuthRequiredControllerInterface &&
            !($currentUser instanceof User)
        ) {
            $event->setController(function() {
                $redirectUrl = $this->urlGenerator->generate('login');
                return new RedirectResponse($redirectUrl);
            });
        }
    }
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['onControllerRequest'],
        ];
    }
}
