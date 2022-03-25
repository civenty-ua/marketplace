<?php

namespace App\Twig\Admin;

use App\Entity\User;
use App\Entity\UserToUserReview;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class UserToUserReviewExtension extends AbstractExtension
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    public function getFilters(): array
    {
        return [
            new TwigFilter('userToUserReview', [$this, 'getUserToUserReview']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('userToUserReview', [$this, 'getUserToUserReview']),
        ];
    }

    /**
     * @param  int $id
     * @return UserToUserReview[]|array|object[]
     */
    public function getUserToUserReview(UserInterface $user)
    {
        return $this->em->getRepository(UserToUserReview::class)->findBy(['user' => $user]);
    }
}