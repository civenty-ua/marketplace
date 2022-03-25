<?php

namespace App\Service\FileManager\Mapping;

use App\Service\FileManager\Namer\NamerInterface;

interface FileMappingInterface
{
    public static function getPath(): string;
    public static function getMappingName(): string;
    public static function getMappingClassName(): string;
    public static function getNamer(): ?NamerInterface;
}
