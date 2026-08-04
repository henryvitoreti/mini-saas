<?php

namespace App\Support\Formatters;

class PersonalDataFormatter
{
    public static function onlyNumbers(string|null $value): string|null
    {
        if ($value === null) {
            return null;
        }

        return preg_replace('/\D/', '', $value);
    }

    public static function onlyAlphaNumeric(string|null $value): string|null
    {
        if ($value === null) {
            return null;
        }

        return strtoupper((string) preg_replace('/[^a-zA-Z0-9]/', '', $value));
    }

    public static function cpf(string|null $value): string|null
    {
        $value = self::onlyNumbers($value);

        if ($value === null || strlen($value) !== 11) {
            return $value;
        }

        return substr($value, 0, 3).'.'.substr($value, 3, 3).'.'.substr($value, 6, 3).'-'.substr($value, 9, 2);
    }

    public static function cnpj(string|null $value): string|null
    {
        $value = self::onlyAlphaNumeric($value);

        if ($value === null || strlen($value) !== 14) {
            return $value;
        }

        return substr($value, 0, 2).'.'.substr($value, 2, 3).'.'.substr($value, 5, 3).'/'.substr($value, 8, 4).'-'.substr($value, 12, 2);
    }

    public static function document(string|null $value): string|null
    {
        $value = self::onlyAlphaNumeric($value);

        if ($value === null) {
            return null;
        }

        return strlen($value) === 14 ? self::cnpj($value) : self::cpf($value);
    }

    public static function phone(string|null $value): string|null
    {
        $value = self::onlyNumbers($value);

        if ($value === null) {
            return null;
        }

        if (strlen($value) === 10) {
            return '('.substr($value, 0, 2).') '.substr($value, 2, 4).'-'.substr($value, 6, 4);
        }

        if (strlen($value) === 11) {
            return '('.substr($value, 0, 2).') '.substr($value, 2, 1).' '.substr($value, 3, 4).'-'.substr($value, 7, 4);
        }

        return $value;
    }

    public static function zipCode(string|null $value): string|null
    {
        $value = self::onlyNumbers($value);

        if ($value === null || strlen($value) !== 8) {
            return $value;
        }

        return substr($value, 0, 5).'-'.substr($value, 5, 3);
    }
}
