<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;

/**
 * @ORM\Entity(repositoryClass=CommentRepository::class)
 */
class Comment implements TimestampableInterface
{
    use TimestampableTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Item::class, inversedBy="comments")
     */
    private $item;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="comments")
     */
    private $authorizedUser;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $anonymousUser;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $message;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(?Item $item): self
    {
        $this->item = $item;

        return $this;
    }

    public function getAuthorizedUser(): ?User
    {
        return $this->authorizedUser;
    }

    public function setAuthorizedUser(?User $authorizedUser): self
    {
        $this->authorizedUser = $authorizedUser;
        $this->anonymousUser = null;

        return $this;
    }

    public function getAnonymousUser(): ?string
    {
        return $this->anonymousUser;
    }

    public function setAnonymousUser(?string $anonymousUser): self
    {
        $this->authorizedUser = null;
        $this->anonymousUser = $anonymousUser;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getTitle();
    }

    public function getTitle(): string
    {
        $userTitle = $this->getUserTitle();
        $createdDate = $this->getCreatedAt()->format('d.m.Y H:i:s');

        return "$userTitle [$createdDate]";
    }

    public function getUserTitle(): string
    {
        return $this->getAuthorizedUser()
            ? $this->getAuthorizedUser()->getName()
            : 'Anonymous';
    }
}
