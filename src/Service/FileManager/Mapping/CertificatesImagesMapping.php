<?php

namespace App\Service\FileManager\Mapping;

final class CertificatesImagesMapping extends FileMapping
{
    public static function getPath(): string
    {
        return '/certificates/files';
    }
}
