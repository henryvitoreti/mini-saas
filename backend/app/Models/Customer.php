<?php

namespace App\Models;

use App\Enums\PersonType;
use App\Rules\ValidateDocument;
use App\Support\Formatters\DateFormatter;
use App\Support\Formatters\PersonalDataFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class Customer extends Model
{
    use SoftDeletes;

    protected $table = 'customers';

    protected $fillable = [
        'name',
        'document',
        'email',
        'type',
        'birth_date',
        'phone',
        'secondary_phone',
        'zip_code',
        'street',
        'number',
        'complement',
        'district',
        'city',
        'state',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => PersonType::class,
            'birth_date' => 'date',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public static function rules(int|null $id = null, PersonType|string|null $type = null): array
    {
        $type = $type ?? PersonType::INDIVIDUAL;
        $documentUniqueRule = Rule::unique('customers', 'document')->whereNull('deleted_at');
        $emailUniqueRule = Rule::unique('customers', 'email')->whereNull('deleted_at');
        $documentOwnerRule = null;

        if ($id !== null) {
            $documentOwnerRule = Rule::exists('customers', 'document')
                ->where('id', $id)
                ->whereNull('deleted_at');
            $emailUniqueRule->ignore($id);
        }

        return [
            'name' => ['required', 'string', 'max:60'],
            'document' => array_values(array_filter([
                'required',
                'min:11',
                'max:14',
                new ValidateDocument($type),
                $documentOwnerRule ?? $documentUniqueRule,
            ])),
            'email' => ['required', 'email', 'max:60', $emailUniqueRule],
            'type' => ['required', Rule::in([PersonType::INDIVIDUAL->value, PersonType::COMPANY->value])],
            'birth_date' => ['nullable', 'date'],
            'phone' => ['required', 'min:10', 'max:11'],
            'secondary_phone' => ['nullable', 'min:10', 'max:11'],
            'zip_code' => ['required', 'size:8'],
            'street' => ['required', 'string', 'max:100'],
            'number' => ['required_without:complement', 'string', 'max:6'],
            'complement' => ['required_without:number', 'string', 'max:200'],
            'district' => ['required', 'string', 'max:60'],
            'city' => ['required', 'string', 'max:75'],
            'state' => ['required', 'string', 'max:75'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function getFormattedDocumentAttribute(): string|null
    {
        return PersonalDataFormatter::document($this->document);
    }

    public function getFormattedPhoneAttribute(): string|null
    {
        return PersonalDataFormatter::phone($this->phone);
    }

    public function getFormattedSecondaryPhoneAttribute(): string|null
    {
        return PersonalDataFormatter::phone($this->secondary_phone);
    }

    public function getFormattedZipCodeAttribute(): string|null
    {
        return PersonalDataFormatter::zipCode($this->zip_code);
    }

    public function getFormattedBirthDateAttribute(): string|null
    {
        return DateFormatter::databaseToBr($this->birth_date);
    }

    public function getFormattedCreatedAtAttribute(): string|null
    {
        return DateFormatter::databaseToBr($this->created_at, true);
    }

    public function getFormattedUpdatedAtAttribute(): string|null
    {
        return DateFormatter::databaseToBr($this->updated_at, true);
    }
}
