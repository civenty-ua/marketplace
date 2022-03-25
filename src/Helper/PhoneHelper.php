<?php

namespace App\Helper;

class PhoneHelper
{
    public static function clearPhone(string $phone): string
    {
        return str_replace([' ', '(', ')', '+'], '', $phone);
    }

    public static function getFullPhone(string $phone, $clear = true)
    {
        if ($clear) {
            $phone = self::clearPhone($phone);
        }

        if (substr($phone,0,2) != 38) {
            $phone = '38' . $phone;
        }

        return $phone;
    }

    public static function getPhonesArray($phones, $clear = true, $full = true)
    {
        if (!is_array($phones) && is_string($phones)) {
            $phones = [$phones];
        }

        if ($clear || $full) {
            foreach ($phones as &$phone) {
                if ($full) {
                    $phone = self::getFullPhone($phone, $clear);
                } else {
                    $phone = self::clearPhone($phone);
                }
            }
        }

        return $phones;
    }
}
