<?php

namespace Tests\Unit\Support\Formatters;

use App\Support\Formatters\PersonalDataFormatter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PersonalDataFormatterTest extends TestCase
{
    public static function formats(): array
    {
        return [
            ['onlyNumbers', 'a12.3-4', '1234'], ['onlyNumbers', null, null], ['onlyNumbers', '', ''],
            ['onlyAlphaNumeric', 'ab.12-cd/34', 'AB12CD34'], ['onlyAlphaNumeric', null, null],
            ['cpf', '52998224725', '529.982.247-25'], ['cpf', '529.982.247-25', '529.982.247-25'],
            ['cpf', '12-3', '123'], ['cpf', null, null],
            ['cnpj', '11222333000181', '11.222.333/0001-81'], ['cnpj', 'ab12cd34ef5690', 'AB.12C.D34/EF56-90'],
            ['cnpj', 'ab-12', 'AB12'], ['cnpj', null, null],
            ['document', '52998224725', '529.982.247-25'], ['document', '11222333000181', '11.222.333/0001-81'],
            ['document', null, null], ['document', '', ''],
            ['phone', '1134567890', '(11) 3456-7890'], ['phone', '11987654321', '(11) 9 8765-4321'],
            ['phone', '(11) 9 8765-4321', '(11) 9 8765-4321'], ['phone', '123', '123'], ['phone', null, null],
            ['zipCode', '01310100', '01310-100'], ['zipCode', '01310-100', '01310-100'],
            ['zipCode', '12-3', '123'], ['zipCode', null, null],
        ];
    }

    #[DataProvider('formats')]
    public function test_formats_and_normalizes_values(string $method, ?string $input, ?string $expected): void
    {
        $this->assertSame($expected, PersonalDataFormatter::$method($input));
    }
}
