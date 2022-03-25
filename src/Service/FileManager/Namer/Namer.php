<?php

namespace App\Service\FileManager\Namer;

use Symfony\Component\HttpFoundation\File\File;

abstract class Namer implements NamerInterface
{
    protected function getExtension(File $file): ?string
    {
        $originalName = $file->getClientOriginalName();

        if ('' !== ($extension = \pathinfo($originalName, \PATHINFO_EXTENSION))) {
            return $extension;
        }

        if ('' !== ($extension = $file->guessExtension())) {
            return $extension;
        }

        return null;
    }

    abstract public function getName(File $file): string;
}
