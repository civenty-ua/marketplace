<?php
declare(strict_types=1);

namespace App\Twig;

use Twig\{
    TwigFilter,
    Markup,
    Extension\AbstractExtension,
};
/**
 * Attributes array to string converter.
 *
 * @package App\Twig
 */
class ToAttributesStringExtension extends AbstractExtension
{
    /**
     * @inheritDoc
     */
    public function getFilters(): array
    {
        return array_merge(parent::getFilters(), [
            new TwigFilter('toAttributesString', [$this, 'toAttributesString']),
        ]);
    }
    /**
     * Convert attributes array(key => value set) to html string
     *
     * @param   array $values               Attributes values set.
     *
     * @return  Markup                      Attributes string (TWIG markup wrapper for raw quotes output).
     */
    public function toAttributesString(array $values): Markup
    {
        $result = [];

        foreach ($values as $key => $value) {
            $valueString = is_array($value)
                ? implode(' ', $value)
                : (string) $value;

            $result[] = strlen($valueString) > 0
                ? "$key=\"$value\""
                : $key;
        }

        return new Markup(implode(' ', $result), 'UTF-8');
    }
}
