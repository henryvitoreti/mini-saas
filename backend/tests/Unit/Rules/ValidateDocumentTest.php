<?php

namespace Tests\Unit\Rules;

use App\Enums\PersonType;
use App\Rules\ValidateDocument;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ValidateDocumentTest extends TestCase
{
    public static function documents(): array
    {
        return [
            [PersonType::INDIVIDUAL, '52998224725', null], ['individual', '529.982.247-25', null],
            ['individual', '11111111111', 'O CPF informado é inválido.'],
            ['individual', '52998224724', 'O CPF informado é inválido.'],
            ['individual', '52998224715', 'O CPF informado é inválido.'],
            ['individual', '', 'O CPF informado é inválido.'], ['individual', '123', 'O CPF informado é inválido.'],
            [PersonType::COMPANY, '11222333000181', null], ['company', '11.222.333/0001-81', null],
            ['company', '00000000000000', 'O CNPJ informado é inválido.'],
            ['company', '11222333000180', 'O CNPJ informado é inválido.'],
            ['company', '11222333000171', 'O CNPJ informado é inválido.'],
            ['company', '112223330001AB', 'O CNPJ informado é inválido.'],
            ['company', '', 'O CNPJ informado é inválido.'], ['company', '123', 'O CNPJ informado é inválido.'],
        ];
    }

    #[DataProvider('documents')]
    public function test_validates_document_and_reports_failures(PersonType|string $type, string $document, ?string $expected): void
    {
        $errors = [];

        (new ValidateDocument($type))->validate('document', $document, function (string $message) use (&$errors): void {
            $errors[] = $message;
        });

        $this->assertSame($expected === null ? [] : [$expected], $errors);
    }
}
