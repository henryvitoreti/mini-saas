<?php

namespace App\Rules;

use App\Enums\PersonType;
use App\Support\Formatters\PersonalDataFormatter;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateDocument implements ValidationRule
{
    public function __construct(private readonly PersonType|string $type)
    {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $type = $this->type instanceof PersonType ? $this->type->value : $this->type;

        if ($type === PersonType::COMPANY->value) {
            $document = PersonalDataFormatter::onlyAlphaNumeric((string) $value);

            if ($document === null || !preg_match('/^[A-Z0-9]{12}\d{2}$/', $document) || !$this->isValidCnpj($document)) {
                $fail('O CNPJ informado é inválido.');
            }

            return;
        }

        $document = PersonalDataFormatter::onlyNumbers((string) $value);

        if ($document === null || strlen($document) !== 11 || !$this->isValidCpf($document)) {
            $fail('O CPF informado é inválido.');
        }
    }

    protected function isValidCpf(string $document): bool
    {
        if (preg_match('/^(\d)\1{10}$/', $document)) {
            return false;
        }

        for ($position = 9; $position < 11; $position++) {
            $sum = 0;

            for ($index = 0; $index < $position; $index++) {
                $sum += (int) $document[$index] * (($position + 1) - $index);
            }

            $digit = ((10 * $sum) % 11) % 10;

            if ((int) $document[$position] !== $digit) {
                return false;
            }
        }

        return true;
    }

    protected function isValidCnpj(string $document): bool
    {
        if (preg_match('/^(\d)\1{13}$/', $document)) {
            return false;
        }

        $weights = [
            [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
            [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
        ];

        for ($digitPosition = 12; $digitPosition < 14; $digitPosition++) {
            $sum = 0;

            foreach ($weights[$digitPosition - 12] as $index => $weight) {
                $sum += (ord($document[$index]) - 48) * $weight;
            }

            $digit = $sum % 11 < 2 ? 0 : 11 - ($sum % 11);

            if ((int) $document[$digitPosition] !== $digit) {
                return false;
            }
        }

        return true;
    }
}
