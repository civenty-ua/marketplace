<?php

namespace App\Entity\Market;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\Market\UserFavoriteRepository;
use App\Entity\User;
/**
 * @ORM\Entity(repositoryClass=UserFavoriteRepository::class)
 * @ORM\Table(name="market_user_favorite")
 */
class UserFavorite
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $user;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="favorites")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $userFavorite;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUserFavorite(): ?User
    {
        return $this->userFavorite;
    }

    public function setUserFavorite(?User $userFavorite): self
    {
        $this->userFavorite = $userFavorite;

        return $this;
    }
}
