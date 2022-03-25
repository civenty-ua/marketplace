<?php

namespace App\Entity\Market;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\Market\CommodityFavoriteRepository;
use App\Entity\User;
/**
 * @ORM\Entity(repositoryClass=CommodityFavoriteRepository::class)
 * @ORM\Table(name="market_commodity_favorite")
 */
class CommodityFavorite
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
     * @ORM\ManyToOne(targetEntity=Commodity::class, inversedBy="favorites")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Commodity $commodity;

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

    public function getCommodity(): ?Commodity
    {
        return $this->commodity;
    }

    public function setCommodity(?Commodity $commodity): self
    {
        $this->commodity = $commodity;

        return $this;
    }
}
