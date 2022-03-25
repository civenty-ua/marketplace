<?php


namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class FileExistsExtension extends AbstractExtension
{


    public function getFilters()
    {
        return [
            new TwigFilter('file_exists', [$this, 'fileExists']),
        ];
    }

    public function fileExists($file)
    {
        return file_exists(realpath(".").$file);
    }
}