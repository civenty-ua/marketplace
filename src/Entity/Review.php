<?php

namespace App\Entity;

use App\Repository\ReviewRepository;
use App\Traits\Trans;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;
use App\Validator as AcmeAssert;

/**
 * @ORM\Entity(repositoryClass=ReviewRepository::class)
 * @AcmeAssert\OnlySixReviews()
 */
class Review implements TranslatableInterface
{
    use TranslatableTrait;
    use Trans;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isTop = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isTop(): bool
    {
        return $this->isTop;
    }

    /**
     * @param bool $isTop
     */
    public function setIsTop(bool $isTop): void
    {
        $this->isTop = $isTop;
    }
}
