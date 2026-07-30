<?php

namespace App\Http\Requests;

use App\Enums\PersonType;
use App\Helpers\ConnectionHelper;
use App\Models\Domain;
use App\Rules\ValidateDocument;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TenantRequest extends FormRequest
{
    private function isUpdating(): bool
    {
        return $this->route('id') !== null;
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $company = is_array($this->input('company')) ? $this->input('company') : [];
        $user = is_array($this->input('user')) ? $this->input('user') : [];

        if ($this->isUpdating()) {
            $this->merge(['company' => $company]);

            return;
        }

        $company['name'] = $company['name'] ?? $this->input('name');
        $company['role_id'] = $company['role_id'] ?? $this->input('role_id', $this->input('company_role_id'));

        $user['name'] = $user['name'] ?? $this->input('user_name');
        $user['email'] = $user['email'] ?? $this->input('user_email');
        $user['password'] = $user['password'] ?? $this->input('user_password');

        $tenantId = (string) $this->input('id', $this->input('domain', ''));

        if ($tenantId === '') {
            $tenantId = (string) ($company['name'] ?? '');
        }

        $this->merge([
            'id' => Str::slug($tenantId),
            'company' => $company,
            'user' => $user,
        ]);
    }

    public function rules(): array
    {
        $rules = [
            'company' => ['required', 'array'],
            'company.role_id' => [
                'required',
                'integer',
                Rule::exists(ConnectionHelper::centralTable('roles'), 'id')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],
            'company.name' => ['required', 'string', 'max:255'],
            'company.email' => ['required', 'email', 'max:255'],
            'company.phone' => ['required', 'string', 'min:10', 'max:11'],
            'company.secondary_phone' => ['nullable', 'string', 'min:10', 'max:11'],
            'company.zip_code' => ['nullable', 'string', 'size:8'],
            'company.street' => ['nullable', 'string', 'max:100'],
            'company.number' => ['nullable', 'string', 'max:20'],
            'company.complement' => ['nullable', 'string', 'max:200'],
            'company.district' => ['nullable', 'string', 'max:60'],
            'company.city' => ['nullable', 'string', 'max:75'],
            'company.state' => ['nullable', 'string', 'size:2'],
            'company.logo_path' => ['nullable', 'string', 'max:255'],
            'company.notes' => ['nullable', 'string'],
        ];

        if ($this->isUpdating()) {
            return [
                'id' => ['prohibited'],
                'company.document' => ['prohibited'],
                'user' => ['prohibited'],
                ...$rules,
            ];
        }

        return [
            ...$rules,
            'id' => [
                'required',
                'string',
                'min:3',
                'max:63',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique(ConnectionHelper::centralTable('tenants'), 'id'),
                $this->uniqueDomainRule(),
            ],
            'company.document' => ['required', 'string', 'size:14', new ValidateDocument(PersonType::COMPANY)],
            'user' => ['required', 'array'],
            'user.name' => ['required', 'string', 'max:255'],
            'user.email' => ['required', 'email', 'max:255'],
            'user.password' => ['required', 'string', 'min:6', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.regex' => 'O identificador deve conter apenas letras minúsculas, números e hífens.',
            'id.prohibited' => 'O identificador do tenant não pode ser alterado.',
            'company.role_id.exists' => 'O perfil de acesso informado não está disponível.',
            'user.prohibited' => 'Os dados do usuário inicial não podem ser alterados por este formulário.',
        ];
    }

    private function uniqueDomainRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $domain = Domain::buildApiDomain((string)$value);

            if (Domain::on(ConnectionHelper::centralConnection())->where('domain', $domain)->exists()) {
                $fail('O domínio informado já está em uso.');
            }
        };
    }
}
