<?php

namespace App\Enums;

enum PersonType: string
{
    case INDIVIDUAL = 'individual';
    case COMPANY = 'company';

    public function getLabel(): string
    {
        return match ($this) {
            self::INDIVIDUAL => 'Pessoa Física',
            self::COMPANY => 'Pessoa Jurídica',
        };
    }
}
