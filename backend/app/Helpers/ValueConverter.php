<?php

namespace App\Helpers;

class ValueConverter
{
    public static function toBoolean(mixed $value): bool
    {
        if (!is_scalar($value) && $value !== null) {
            return false;
        }

        return in_array($value, [true, 1]) || in_array(strtolower((string)$value), ['1', 'true', 'on', 'yes']);
    }
}
