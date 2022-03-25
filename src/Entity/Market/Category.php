<?php
declare(strict_types = 1);

namespace App\Entity\Market;

use App\Traits\Meta;
use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection,
};
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model\SoftDeletable\SoftDeletableTrait;
use App\Repository\Market\CategoryRepository;
/**
 * Категории и субкатгории товаров
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 * @ORM\Table(name="market_category")
 */
class Category
{
    use SoftDeletableTrait,
        Meta;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $title = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $description = null;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="children")
     */
    private ?Category $parent = null;

    /**
     * @ORM\OneToMany(targetEntity=Category::class, mappedBy="parent")
     */
    private Collection $children;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $commodityType = null;

    /**
     * @ORM\OneToMany(targetEntity=CategoryAttributeParameters::class, mappedBy="category", orphanRemoval=true)
     */
    private Collection $categoryAttributesParameters;

    private bool $categoryAttributesSorted = false;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->categoryAttributesParameters = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getTitle() ?? 'category[no title]';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(self $child): self
    {
        if ($this->children->removeElement($child)) {
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    public function getCommodityType(): ?string
    {
        return $this->commodityType;
    }

    public function setCommodityType(string $commodityType): self
    {
        $this->commodityType = $commodityType;

        return $this;
    }

    /**
     * @return Collection|CategoryAttributeParameters[]
     */
    public function getCategoryAttributesParameters(): Collection
    {
        if (!$this->categoryAttributesSorted) {
            $attributesSorted = $this->sortCategoryAttributesParameters($this->categoryAttributesParameters);

            foreach ($this->categoryAttributesParameters as $attribute) {
                $this->removeCategoryAttributeParameters($attribute);
            }
            foreach ($attributesSorted as $attribute) {
                $this->addCategoryAttributeParameters($attribute);
            }

            $this->categoryAttributesSorted = true;
        }

        return $this->categoryAttributesParameters;
    }

    public function addCategoryAttributeParameters(CategoryAttributeParameters $categoryAttributeParameters): self
    {
        if (!$this->categoryAttributesParameters->contains($categoryAttributeParameters)) {
            $this->categoryAttributesParameters[] = $categoryAttributeParameters;
            $categoryAttributeParameters->setCategory($this);
        }

        $this->categoryAttributesSorted = false;
        return $this;
    }

    public function removeCategoryAttributeParameters(CategoryAttributeParameters $categoryAttributeParameters): self
    {
        if ($this->categoryAttributesParameters->removeElement($categoryAttributeParameters)) {
            if ($categoryAttributeParameters->getCategory() === $this) {
                $categoryAttributeParameters->setCategory(null);
            }
        }

        $this->categoryAttributesSorted = false;
        return $this;
    }
    /**
     * Sort attributes parameters set by sort field.
     *
     * @param   Collection|CategoryAttributeParameters[] $attributes    Attributes.
     *
     * @return  array                                                   Attributes sorted.
     */
    private function sortCategoryAttributesParameters(Collection $attributes): array
    {
        $attributesBySortValue = [];

        foreach ($attributes as $attribute) {
            $attributeSortValue = $attribute->getSort() ?? 0;

            while ($attributeSortValue <= 0 || isset($attributesBySortValue[$attributeSortValue])) {
                $attributeSortValue++;
            }

            $attributesBySortValue[$attributeSortValue] = $attribute;
        }

        ksort($attributesBySortValue);

        return $attributesBySortValue;
    }
}
