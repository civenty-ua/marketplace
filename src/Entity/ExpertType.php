<?php

namespace App\Entity;

use App\Repository\ExpertTypeRepository;
use App\Traits\Trans;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;

/**
 * @ORM\Entity(repositoryClass=ExpertTypeRepository::class)
 */
class ExpertType implements TranslatableInterface, TimestampableInterface
{
    use TranslatableTrait;
    use TimestampableTrait;
    use Trans;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity=Expert::class, mappedBy="expertTypes")
     */
    private $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();

    }

    public function __toString(): string
    {
        return $this->proxyCurrentLocaleTranslation('getName');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|Item[]
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(Expert $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
            $item->addExpertType($this);
        }

        return $this;
    }

    public function removeItem(Expert $item): self
    {
        if ($this->items->removeElement($item)) {
            $item->removeExpertType($this);
        }

        return $this;
    }
}
