<?php

namespace App\Http\Requests;

use App\Models\Customer;
use App\Support\Formatters\DateFormatter;
use App\Support\Formatters\PersonalDataFormatter;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'document' => PersonalDataFormatter::onlyNumbers($this->input('document')),
            'phone' => PersonalDataFormatter::onlyNumbers($this->input('phone')),
            'secondary_phone' => PersonalDataFormatter::onlyNumbers($this->input('secondary_phone')),
            'zip_code' => PersonalDataFormatter::onlyNumbers($this->input('zip_code')),
            'birth_date' => DateFormatter::brToDatabase($this->input('birth_date')),
        ]);
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return Customer::rules($id === null ? null : (int) $id, $this->input('type'));
    }

    public function messages(): array
    {
        $response = [];

        if ($this->route('id')) {
            $response = [
                'document.exists' => 'O documento informado não pertence ao cliente selecionado.',
            ];
        }

        return $response;
    }
}
