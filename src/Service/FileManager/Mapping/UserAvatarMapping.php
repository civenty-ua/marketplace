<?php

namespace App\Service\FileManager\Mapping;

final class UserAvatarMapping extends FileMapping
{
    public static function getPath(): string
    {
        return '/user/avatar';
    }
}
