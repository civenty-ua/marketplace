<?php
declare(strict_types = 1);

namespace App\Twig\Market;

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\{
    TwigFilter,
    Extension\AbstractExtension,
};
use App\Entity\Market\{
    Attribute,
    Commodity,
};

use function in_array;
use function array_merge;
/**
 * Market, commodity currency title provider.
 *
 * @package App\Twig
 */
class CommodityCurrencyTitleExtension extends AbstractExtension
{
    private TranslatorInterface                 $translator;
    private CommodityAttributesWorkerExtension  $propertiesWorkerFilter;

    public function __construct(
        TranslatorInterface                 $translator,
        CommodityAttributesWorkerExtension  $propertiesWorkerFilter
    ) {
        $this->translator               = $translator;
        $this->propertiesWorkerFilter   = $propertiesWorkerFilter;
    }
    /**
     * @inheritDoc
     */
    public function getFilters(): array
    {
        return array_merge(parent::getFunctions(), [
            new TwigFilter('getCurrencyTitle', [$this, 'getCurrencyTitle']),
        ]);
    }
    /**
     * Get commodity printable currency value.
     *
     * @param   Commodity $commodity    Commodity.
     *
     * @return  string                  Commodity currency title.
     */
    public function getCurrencyTitle(Commodity $commodity): string
    {
        $currencyTitle      = $this->translator->trans('market.currency.short');
        $specialCodesValues = [];

        foreach ($commodity->getCommodityAttributesValues() as $attributesValue) {
            $specialCode = $attributesValue->getAttribute()->getCode();

            if (
                in_array($specialCode, Attribute::getAvailableSpecialCodes()) &&
                !empty($attributesValue->getValue())
            ) {
                $specialCodesValues[$specialCode] = $this
                    ->propertiesWorkerFilter
                    ->getAttributePrintableValue($attributesValue);
            }
        }

        $measure = $specialCodesValues[Attribute::SPECIAL_CODE_PRICE_MEASURE]
            ?? $specialCodesValues[Attribute::SPECIAL_CODE_MEASURE]
            ?? null;

        return $measure ? "$currencyTitle/$measure" : $currencyTitle;
    }
}
