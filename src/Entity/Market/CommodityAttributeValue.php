<?php

namespace App\Entity\Market;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\Market\CommodityAttributeValueRepository;
use function array_filter;
use function array_map;
use function explode;
use function implode;
use function is_array;

/**
 * @ORM\Entity(repositoryClass=CommodityAttributeValueRepository::class)
 * @ORM\Table(name="market_commodity_attribute_value")
 */
class CommodityAttributeValue
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\ManyToOne(targetEntity=Commodity::class, inversedBy="commodityAttributesValues")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Commodity $commodity;

    /**
     * @ORM\ManyToOne(targetEntity=Attribute::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Attribute $attribute;

    /**
     * @ORM\Column(type="text")
     */
    private $value;

    private $valueNormalized;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAttribute(): ?Attribute
    {
        return $this->attribute;
    }

    public function setAttribute(?Attribute $attribute): self
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function getValue()
    {
        if (!$this->valueNormalized) {
            $this->valueNormalized = $this->convertValueToNormalizedFormat($this->value);
        }

        return $this->valueNormalized;
    }

    public function setValue($value): self
    {
        $this->valueNormalized  = $this->convertValueToNormalizedFormat($value);
        $this->value            = $this->convertValueToDBFormat($this->valueNormalized);

        return $this;
    }
    /**
     * Convert value to normalized workable format, depending on attribute type.
     *
     * @param   mixed $value                Raw value ($value from DB).
     *
     * @return  mixed                       Normalized value.
     */
    private function convertValueToNormalizedFormat($value)
    {
        switch ($this->getAttribute()->getType()) {
            case Attribute::TYPE_LIST:
            case Attribute::TYPE_DICTIONARY:
                return is_numeric($value) ? (int) $value : null;
            case Attribute::TYPE_INT:
                if (!is_numeric($value)) {
                    return null;
                }

                return strpos($value, '.') !== false
                    ? (float)   $value
                    : (int)     $value;
            case Attribute::TYPE_LIST_MULTIPLE:
                $valuesSet          = is_array($value)
                    ? $value
                    : explode(',', (string) $value);
                $valuesNormalized   = array_map(function($subValue): int {
                    return (int) $subValue;
                }, $valuesSet);

                return array_filter($valuesNormalized, function(int $subValue): bool {
                    return $subValue > 0;
                });
            default:
                return $value;
        }
    }
    /**
     * Convert value to DB format, depending on attribute type.
     *
     * @param   mixed $value                Value.
     *
     * @return  mixed                       Converted value.
     */
    private function convertValueToDBFormat($value)
    {
        switch ($this->getAttribute()->getType()) {
            case Attribute::TYPE_LIST_MULTIPLE:
                return implode(',', (array) $value);
            default:
                return (string) $value;
        }
    }
}
