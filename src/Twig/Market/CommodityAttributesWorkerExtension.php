<?php
declare(strict_types = 1);

namespace App\Twig\Market;

use Twig\{
    TwigFilter,
    Extension\AbstractExtension,
};
use App\Twig\NumberFormatExtension;
use App\Entity\Market\{
    Attribute,
    CategoryAttributeParameters,
    CommodityAttributeValue,
    Commodity,
    CommodityProduct,
    CommodityService,
};
/**
 * Market, commodities attributes printable values provider.
 *
 * @package App\Twig
 */
class CommodityAttributesWorkerExtension extends AbstractExtension
{
    private AttributesDictionariesExtension $attributesDictionariesFilter;
    private NumberFormatExtension           $numberFormatFilter;

    public function __construct(
        AttributesDictionariesExtension $attributesDictionariesFilter,
        NumberFormatExtension           $numberFormatFilter
    ) {
        $this->attributesDictionariesFilter = $attributesDictionariesFilter;
        $this->numberFormatFilter           = $numberFormatFilter;
    }
    /**
     * @inheritDoc
     */
    public function getFilters(): array
    {
        return array_merge(parent::getFunctions(), [
            new TwigFilter('getAttributePrintableValue', [$this, 'getAttributePrintableValue']),
            new TwigFilter('getCommoditySpecialAttribute', [$this, 'getCommoditySpecialAttribute']),
        ]);
    }
    /**
     * Get attribute printable value.
     *
     * @param   CommodityAttributeValue $attributeValue Attribute value.
     *
     * @return  string|null                             Attribute printable value.
     */
    public function getAttributePrintableValue(CommodityAttributeValue $attributeValue): ?string
    {
        $attributeParameters = $this->findAttributeParameters($attributeValue);

        if (!$attributeParameters) {
            return null;
        }

        switch ($attributeValue->getAttribute()->getType()) {
            case Attribute::TYPE_LIST:
                foreach ($attributeParameters->getCategoryAttributeListValues() as $listValue) {
                    if ($listValue->getId() === $attributeValue->getValue()) {
                        return $listValue->getValue();
                    }
                }

                return null;
            case Attribute::TYPE_LIST_MULTIPLE:
                $values = [];

                foreach ($attributeParameters->getCategoryAttributeListValues() as $listValue) {
                    if (in_array($listValue->getId(), $attributeValue->getValue())) {
                        $values[] = $listValue->getValue();
                    }
                }

                return count($values) > 0
                    ? implode(', ', $values)
                    : null;
            case Attribute::TYPE_DICTIONARY:
                $dictionaries   = $this->attributesDictionariesFilter->getAttributesDictionaries();
                $dictionaryName = $attributeValue->getAttribute()->getDictionary();

                return $dictionaries[$dictionaryName][$attributeValue->getValue()] ?? null;
            case Attribute::TYPE_INT:
                $value = (string) $attributeValue->getValue();

                return is_numeric($value)
                    ? $this->numberFormatFilter->numberFormat($value)
                    : null;
            default:
                $value = (string) $attributeValue->getValue();

                return strlen($value) > 0 ? $value : null;
        }
    }
    /**
     * Get commodity special attribute value, if any.
     *
     * @param   Commodity   $commodity          Commodity.
     * @param   string      $code               Attribute special code.
     * @param   bool        $isOnListPage       Attribute is visible on lis page.
     *
     * @return  CommodityAttributeValue|null    Commodity attribute value.
     */
    public function getCommoditySpecialAttribute(
        Commodity   $commodity,
        string      $code,
        bool        $isOnListPage = false
    ): ?CommodityAttributeValue {
        foreach ($commodity->getCommodityAttributesValues() as $attributeValue) {
            $attributeParameters    = $this->findAttributeParameters($attributeValue);
            $attributeIsOnListPage  = $attributeParameters && $attributeParameters->getShowOnList();

            if (
                $attributeValue->getAttribute()->getCode() === $code
                && (!$isOnListPage || $attributeIsOnListPage)
            ) {
                return $attributeValue;
            }
        }

        return null;
    }
    /**
     * @param CommodityAttributeValue $attributeValue
     *
     * @return CategoryAttributeParameters
     */
    private function findAttributeParameters(
        CommodityAttributeValue $attributeValue
    ): ?CategoryAttributeParameters {
        /** @var CommodityProduct|CommodityService $commodity */
        $commodity              = $attributeValue->getCommodity();
        $attributesParameters   = $commodity->getCategory()->getCategoryAttributesParameters();

        foreach ($attributesParameters as $attributeParameters) {
            if ($attributeParameters->getAttribute() === $attributeValue->getAttribute()) {
                return $attributeParameters;
            }
        }

        return null;
    }
}
