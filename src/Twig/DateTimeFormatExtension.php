<?php
declare(strict_types = 1);

namespace App\Twig;

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use DateTime;

/**
 * AGRO dateTime formatter.
 *
 * @package App\Twig
 */
class DateTimeFormatExtension extends AbstractExtension
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @inheritDoc
     */
    public function getFilters(): array
    {
        return array_merge(parent::getFilters(), [
            new TwigFilter('dateFormatAgro', [$this, 'dateFormat']),
        ]);
    }

    /**
     * Format date.
     *
     * @param   DateTime $value               Value as is.
     *
     * @return  string                      Formatted date.
     */
    public function dateFormat(DateTime $value): string
    {
        $date = $value->format('d M Y');
        $day = date('d',strtotime($date));
        $month = date('M',strtotime($date));
        $year = date('Y',strtotime($date));
        return "$day " . $this->translator->trans($month) . " $year";
    }
}