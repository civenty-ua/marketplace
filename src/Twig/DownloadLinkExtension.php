<?php


namespace App\Twig;

use Symfony\Component\Security\Core\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DownloadLinkExtension extends AbstractExtension
{

    public function getFilters()
    {
        return [
            new TwigFilter('is_download_link', [$this, 'isDownloadLink']),
        ];
    }

    public function isDownloadLink($link)
    {
        return str_starts_with($link, '/upload');
    }
}