<?php

namespace App\Entity\Market\Notification;

use App\Entity\Market\Commodity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\Market\Notification\PriceOfferNotificationRepository;

/**
 * @ORM\Entity(repositoryClass=PriceOfferNotificationRepository::class)
 * @ORM\Table(name="market_notification_price_offer")
 */
class PriceOfferNotification  extends Notification
{
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(
     *      min = 2,
     *      max = 255,
     *      minMessage = "Name must be at least {{ limit }} characters long",
     *      maxMessage = "Name cannot be longer than {{ limit }} characters"
     * )
     */
    private ?string $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Email()
     */
    private ?string $email;

    /**
     * @ORM\Column(type="float")
     * @Assert\PositiveOrZero()
     */
    private ?float $price;

    /**
     * @ORM\ManyToOne(targetEntity=Commodity::class)
     */
    private ?Commodity $commodity;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

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
    public function getType(): string
    {
        return 'Пропозиція ціни';
    }
}
