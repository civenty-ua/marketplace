<?php

namespace App\Event;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener
{
    private EntityManagerInterface $entityManager;

    private function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        if ($user instanceof UserInterface) {
            $user = $this->entityManager->getRepository(User::class)->find($user->getId());
            if ($user) {
                $user->setIsOnline(true);
                $this->entityManager->flush();
            }
        }
    }
}