<?php

namespace App\Entity;

use App\Repository\DeadUrlRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Validator as AcmeAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=DeadUrlRepository::class)
 * @UniqueEntity("deadRequest")
 * @AcmeAssert\DeadUrlConstraint()
 * @ORM\Table(indexes={@ORM\Index(name="url_checksum_idx",columns={"check_sum"}, options={"length": 255})})
 */
class DeadUrl
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=2000, nullable=true)
     */
    private $deadRequest;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $redirectTo;

    /**
     * @ORM\Column(type="boolean", nullable=true,options={"default" : false})
     */
    private $isActive;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $attemptAmount = 0;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $checkSum;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDeadRequest(): ?string
    {
        return $this->deadRequest;
    }

    public function setDeadRequest(?string $deadRequest): self
    {
        $this->deadRequest = $deadRequest;

        return $this;
    }

    public function getRedirectTo(): ?string
    {
        return $this->redirectTo;
    }

    public function setRedirectTo(?string $redirectTo): self
    {
        $this->redirectTo = $redirectTo;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getAttemptAmount(): int
    {
        return $this->attemptAmount ?? 0;
    }

    public function setAttemptAmount(int $attemptAmount): self
    {
        $this->attemptAmount = $attemptAmount;

        return $this;
    }

    public function increaseAttemptAmount(): self
    {
        $this->setAttemptAmount($this->getAttemptAmount() + 1);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCheckSum()
    {
        return $this->checkSum;
    }

    /**
     * @param mixed $checkSum
     */
    public function setCheckSum($checkSum): void
    {
        $this->checkSum = $checkSum;
    }

    /**
     * @return DateTimeInterface
     */
    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(): void
    {
        $this->createdAt = new \DateTime("now",new \DateTimeZone('Europe/Kiev'));
    }
}
