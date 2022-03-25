<?php

namespace App\Entity\Market;

use App\Repository\Market\PhoneRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;

/**
 * @ORM\Entity(repositoryClass=PhoneRepository::class)
 * @ORM\Table(name="market_phone")
 */
class Phone
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @App\Validator\Phone
     */
    private $phone;

    /**
     * @ORM\Column(type="boolean",nullable=true)
     */
    private $isMain;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isTelegram;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isViber;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isWhatsApp;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="phones")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    public function __toString() {
        return $this->phone;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getIsMain(): ?bool
    {
        return $this->isMain;
    }

    public function setIsMain(bool $isMain): self
    {
        $this->isMain = $isMain;

        return $this;
    }

    public function getIsTelegram(): ?bool
    {
        return $this->isTelegram;
    }

    public function setIsTelegram(?bool $isTelegram): self
    {
        $this->isTelegram = $isTelegram;

        return $this;
    }

    public function getIsViber(): ?bool
    {
        return $this->isViber;
    }

    public function setIsViber(?bool $isViber): self
    {
        $this->isViber = $isViber;

        return $this;
    }

    public function getIsWhatsApp(): ?bool
    {
        return $this->isWhatsApp;
    }

    public function setIsWhatsApp(?bool $isWhatsApp): self
    {
        $this->isWhatsApp = $isWhatsApp;

        return $this;
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
}
