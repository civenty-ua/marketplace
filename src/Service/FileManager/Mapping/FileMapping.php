<?php

namespace App\Service\FileManager\Mapping;

use App\Service\FileManager\Namer\NamerInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

abstract class FileMapping implements FileMappingInterface
{
    abstract public static function getPath(): string;

    public static function getMappingName(): string
    {
        $camelCaseToSnakeCaseNameConverter = new CamelCaseToSnakeCaseNameConverter();
        $mappingName = $camelCaseToSnakeCaseNameConverter->normalize(self::getMappingClassName());
        return str_replace('_mapping', '', $mappingName);
    }

    public static function getMappingClassName(): string
    {
        $reflectionClass = new \ReflectionClass(static::class);
        return $reflectionClass->getShortName();
    }

    public static function getNamer(): ?NamerInterface
    {
        return null;
    }
}
