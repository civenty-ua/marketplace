<?php
declare(strict_types=1);

namespace App\Entity\Market;

use Collator;
use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection,
};
use Doctrine\ORM\Mapping as ORM;
use App\Repository\Market\CategoryAttributeParametersRepository;
/**
 * @ORM\Entity(repositoryClass=CategoryAttributeParametersRepository::class)
 * @ORM\Table(name="market_category_attribute_parameters")
 */
class CategoryAttributeParameters
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="categoryAttributesParameters")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Category $category = null;

    /**
     * @ORM\ManyToOne(targetEntity=Attribute::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Attribute $attribute = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $required = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $showOnList = null;

    /**
     * @ORM\Column(type="integer")
     */
    private ?int $sort = null;

    /**
     * @ORM\OneToMany(targetEntity=CategoryAttributeListValue::class, mappedBy="categoryAttribute", orphanRemoval=true)
     */
    private Collection $categoryAttributeListValues;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $listSortAlphabetic = null;

    private bool $listSorted = false;

    public function __construct()
    {
        $this->categoryAttributeListValues = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getAttribute(): ?Attribute
    {
        return $this->attribute;
    }

    public function setAttribute(?Attribute $attribute): self
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function getRequired(): bool
    {
        return $this->required ?? false;
    }

    public function setRequired(?bool $required): self
    {
        $this->required = $required;

        return $this;
    }

    public function getShowOnList(): bool
    {
        return $this->showOnList ?? false;
    }

    public function setShowOnList(?bool $showOnList): self
    {
        $this->showOnList = $showOnList;

        return $this;
    }

    public function getSort(): ?int
    {
        return $this->sort;
    }

    public function setSort(int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return Collection|CategoryAttributeListValue[]
     */
    public function getCategoryAttributeListValues(): Collection
    {
        if ($this->getListSortAlphabetic() && !$this->listSorted) {
            $this->categoryAttributeListValues = $this->getSortedList($this->categoryAttributeListValues);
            $this->listSorted = true;
        }

        return $this->categoryAttributeListValues;
    }

    public function addCategoryAttributeListValue(CategoryAttributeListValue $categoryAttributeListValue): self
    {
        $this->listSorted = false;

        if (!$this->categoryAttributeListValues->contains($categoryAttributeListValue)) {
            $this->categoryAttributeListValues[] = $categoryAttributeListValue;
            $categoryAttributeListValue->setCategoryAttribute($this);
        }

        return $this;
    }

    public function removeCategoryAttributeListValue(CategoryAttributeListValue $categoryAttributeListValue): self
    {
        $this->listSorted = false;

        if ($this->categoryAttributeListValues->removeElement($categoryAttributeListValue)) {
            if ($categoryAttributeListValue->getCategoryAttribute() === $this) {
                $categoryAttributeListValue->setCategoryAttribute(null);
            }
        }

        return $this;
    }

    public function getListSortAlphabetic(): bool
    {
        return $this->listSortAlphabetic ?? false;
    }

    public function setListSortAlphabetic(?bool $listSortAlphabetic): self
    {
        $this->listSortAlphabetic = $listSortAlphabetic;

        return $this;
    }
    /**
     * Sort list collection alphabetically.
     *
     * @param   Collection $collection      List values collection.
     *
     * @return  Collection                  Collection sorted.
     */
    private function getSortedList(Collection $collection): Collection
    {
        $valuesMap = [];

        /** @var CategoryAttributeListValue $listValue */
        foreach ($collection as $listValue) {
            $valuesMap[$listValue->getValue()] = $listValue;
            $collection->removeElement($listValue);
        }

        $values = array_keys($valuesMap);
        $sorter = new Collator($this->getCurrentLocale());
        $sorter->sort($values);

        foreach ($values as $listValue) {
            $collection->add($valuesMap[$listValue]);
        }

        return $collection;
    }
    /**
     * Try to get current locale.
     *
     * @return string   Current locale.
     */
    private function getCurrentLocale(): string
    {
        //TODO
        return 'uk_UA';
    }
}
