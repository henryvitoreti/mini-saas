<?php

namespace Tests\Unit\Support\Formatters;

use App\Support\Formatters\DateFormatter;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DateFormatterTest extends TestCase
{
    public static function brazilianDates(): array
    {
        return [[null, null], ['', null], ['15/05/1990', '1990-05-15'], ['29/02/2024', '2024-02-29'],
            ['2026-10-21', '2026-10-21'], ['invalid', 'invalid']];
    }

    #[DataProvider('brazilianDates')]
    public function test_br_to_database(?string $input, ?string $expected): void
    {
        $this->assertSame($expected, DateFormatter::brToDatabase($input));
    }

    public static function databaseDates(): array
    {
        return [[null, false, null], ['', true, null], ['1990-05-15', false, '15/05/1990'],
            ['2026-10-21 14:30:45', true, '21/10/2026 14:30:45'], ['2026-10-21 14:30:45', false, '21/10/2026']];
    }

    #[DataProvider('databaseDates')]
    public function test_database_to_br(?string $input, bool $withTime, ?string $expected): void
    {
        $this->assertSame($expected, DateFormatter::databaseToBr($input, $withTime));
    }

    public function test_carbon_input_is_formatted_without_mutation(): void
    {
        $date = new CarbonImmutable('2026-10-21 14:30:45', 'America/Sao_Paulo');
        $this->assertSame('21/10/2026 14:30:45', DateFormatter::databaseToBr($date, true));
        $this->assertSame('2026-10-21T14:30:45-03:00', $date->toIso8601String());
    }
}
