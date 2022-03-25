<?php

namespace App\Service\FileManager\Mapping;

final class CategoryImageMapping extends FileMapping
{
    public static function getPath(): string
    {
        return '/category/image';
    }
}
