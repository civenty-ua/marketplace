<?php

namespace App\Entity;

use App\Repository\TagsRepository;
use App\Traits\Trans;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;

/**
 * @ORM\Entity(repositoryClass=TagsRepository::class)
 */
class Tags implements TranslatableInterface, TimestampableInterface
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
     * @ORM\ManyToMany(targetEntity=Item::class, mappedBy="tags")
     */
    private $items;

    /**
     * @ORM\ManyToMany(targetEntity=Expert::class, mappedBy="tags")
     */
    private $experts;

    /**
     * @ORM\ManyToMany(targetEntity=Category::class, mappedBy="tags")
     */
    private $categories;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $slug;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->experts = new ArrayCollection();
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

    public function addItem(Item $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
            $item->addTag($this);
        }

        return $this;
    }

    public function removeItem(Item $item): self
    {
        if ($this->items->removeElement($item)) {
            $item->removeTag($this);
        }

        return $this;
    }

    /**
     * @return Collection|Expert[]
     */
    public function getExperts(): Collection
    {
        return $this->experts;
    }

    public function getExpertsCount(): int
    {
        return $this->experts->count();
    }

    public function addExpert(Expert $expert): self
    {
        if (!$this->experts->contains($expert)) {
            $this->experts[] = $expert;
        }

        return $this;
    }

    public function removeExpert(Expert $expert): self
    {
        $this->experts->removeElement($expert);

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection|Category[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
            $category->addTag($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        if ($this->categories->removeElement($category)) {
            $category->removeTag($this);
        }

        return $this;
    }
}
