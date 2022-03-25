<?php

namespace App\Twig\Admin;

use App\Entity\Market\Notification\BidOffer;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class BidOfferExtension extends AbstractExtension
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    public function getFilters(): array
    {
        return [
            new TwigFilter('bidOffer', [$this, 'getBidOffer']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('bidOffer', [$this, 'getBidOffer']),
        ];
    }

    /**
     * @return BidOffer[]|array|object[]
     */
    public function getBidOffer(UserInterface $user)
    {
        return $this->em->getRepository(BidOffer::class)
            ->createQueryBuilder('bo')
            ->orWhere('bo.sender =:user')
            ->orWhere('bo.receiver = :user')
            ->setParameter('user',$user)
            ->addOrderBy('bo.sender','ASC')
            ->addOrderBy('bo.createdAt','DESC')
            ->getQuery()
            ->getResult();
    }
}