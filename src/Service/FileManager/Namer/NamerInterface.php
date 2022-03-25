<?php

namespace App\Service\FileManager\Namer;

use Symfony\Component\HttpFoundation\File\File;

interface NamerInterface
{
    public function getName(File $file): string;
}
