<?php

namespace App\Service\FileManager\Namer;

use Symfony\Component\HttpFoundation\File\File;

class UniqidNamer extends Namer
{
    public function getName(File $file): string
    {
        $name = \str_replace('.', '', \uniqid('', true));
        $extension = $this->getExtension($file);
        if (\is_string($extension) && '' !== $extension) {
            $name = \sprintf('%s.%s', $name, $extension);
        }

        return $name;
    }
}
