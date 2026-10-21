<?php

namespace Tests\Unit\Helpers;

use App\Helpers\ValueConverter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

class ValueConverterTest extends TestCase
{
    public static function values(): array
    {
        return [
            'true' => [true, true], 'false' => [false, false], 'one' => [1, true], 'zero' => [0, false],
            'string one' => ['1', true], 'string zero' => ['0', false], 'true string' => ['true', true],
            'uppercase true' => ['TRUE', true], 'on' => ['on', true], 'yes' => ['yes', true],
            'false string' => ['false', false], 'off' => ['off', false], 'no' => ['no', false],
            'empty' => ['', false], 'null' => [null, false], 'array' => [[], false], 'object' => [new stdClass, false],
        ];
    }

    #[DataProvider('values')]
    public function test_converts_supported_values(mixed $value, bool $expected): void
    {
        $this->assertSame($expected, ValueConverter::toBoolean($value));
    }
}
