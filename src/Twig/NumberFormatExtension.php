<?php
declare(strict_types = 1);

namespace App\Twig;

use Twig\{
    TwigFilter,
    Extension\AbstractExtension,
};
use function is_numeric;

/**
 * AGRO number formatter.
 *
 * @package App\Twig
 */
class NumberFormatExtension extends AbstractExtension
{
    /**
     * @inheritDoc
     */
    public function getFilters(): array
    {
        return array_merge(parent::getFilters(), [
            new TwigFilter('numberFormatAgro', [$this, 'numberFormat']),
        ]);
    }
    /**
     * Format number.
     *
     * @param   string $value               Value as is.
     *
     * @return  string                      Formatted number.
     */
    public function numberFormat(string $value): string
    {
        if (!is_numeric($value)) {
            return $value;
        }

        $isFloat = strpos($value, '.') !== false || strpos($value, ',') !== false;

        return number_format(
            (float) $value,
            $isFloat ? 2 : 0,
            '.',
            ' '
        );
    }
}
