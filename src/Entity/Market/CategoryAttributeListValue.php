<?php

namespace App\Entity\Market;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\Market\CategoryAttributeListValueRepository;
/**
 * @ORM\Entity(repositoryClass=CategoryAttributeListValueRepository::class)
 * @ORM\Table(name="market_category_attribute_list_value")
 */
class CategoryAttributeListValue
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\ManyToOne(targetEntity=CategoryAttributeParameters::class, inversedBy="categoryAttributeListValues")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?CategoryAttributeParameters $categoryAttribute;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $value;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategoryAttribute(): ?CategoryAttributeParameters
    {
        return $this->categoryAttribute;
    }

    public function setCategoryAttribute(?CategoryAttributeParameters $categoryAttribute): self
    {
        $this->categoryAttribute = $categoryAttribute;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }
}
