<?php

namespace App\Twig;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Security\Core\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Wa72\HtmlPageDom\HtmlPage;
use Wa72\HtmlPageDom\HtmlPageCrawler;

class DownloadFilterExtension extends AbstractExtension
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('fileparser', [$this, 'fileParser']),
        ];
    }

    public function fileParser($text)
    {
        if (!empty($text)) {
            $user = $this->security->getUser();
            $crawler = new HtmlPageCrawler($text);
            /** @var HtmlPageCrawler $a */
            $a = $crawler->filter('a');

            if ($a->count() > 0) {
                foreach ($a->getIterator() as $item) {
                    $href = $item->getAttribute('href');
                    if (!empty($href) and str_starts_with($href, '/uploads/')) {
                        $newCrawler = new HtmlPageCrawler($item);
                        $newCrawler->addClass('file-link square-button green-bg-button');
                        if (!$user) {
                            $item->setAttribute('href', '#');
                        }
                    }
                }
            }

            echo $crawler->saveHTML();
        } else {
            echo $text;
        }
    }
}