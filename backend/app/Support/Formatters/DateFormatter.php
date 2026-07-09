<?php

namespace App\Support\Formatters;

use Carbon\CarbonInterface;
use Exception;
use Illuminate\Support\Carbon;

class DateFormatter
{
    public static function brToDatabase(string|null $value): string|null
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
            return $value;
        }

        try {
            return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
        } catch (Exception) {
            return $value;
        }
    }

    public static function databaseToBr(CarbonInterface|string|null $value, bool $withTime = false): string|null
    {
        if ($value === null || $value === '') {
            return null;
        }

        $date = $value instanceof CarbonInterface
            ? $value
            : Carbon::parse($value);

        return $date->format($withTime ? 'd/m/Y H:i:s' : 'd/m/Y');
    }
}
