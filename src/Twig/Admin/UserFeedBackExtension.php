<?php

namespace App\Twig\Admin;

use App\Entity\UserItemFeedback;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class UserFeedBackExtension extends AbstractExtension
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    public function getFilters(): array
    {
        return [
            new TwigFilter('userFeedback', [$this, 'getUserFeedback']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('userFeedback', [$this, 'getUserFeedback']),
        ];
    }

    /**
     * @return UserItemFeedback[]|array|object[]
     */
    public function getUserFeedback(UserInterface $user)
    {
        return $this->em->getRepository(UserItemFeedback::class)->findBy(['user' => $user]);
    }
}