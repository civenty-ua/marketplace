<?php

namespace App\Event;


use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

class LogoutListener implements LogoutHandlerInterface {

    private EntityManagerInterface $entityManager;

    private function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function logout(Request $request, Response $response, TokenInterface $token): void
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return;
        }

        $user = $this->entityManager->getRepository(User::class)->find($user->getId());
        $user->setIsOnline(false);
        $this->entityManager->flush();
    }
}