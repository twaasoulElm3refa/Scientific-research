<?php

namespace App\Support;

final class LookupName
{
    public static function clean(string $name): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $name));
    }

    public static function comparable(string $name): string
    {
        return mb_strtolower(self::clean($name));
    }
}
