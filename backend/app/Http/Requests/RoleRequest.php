<?php

namespace App\Http\Requests;

use App\Helpers\ValueConverter;
use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $permissions = $this->input('permissions', []);

        if (is_array($permissions)) {
            foreach ($permissions as &$permission) {
                if (!is_array($permission)) {
                    continue;
                }

                if (ValueConverter::toBoolean($permission['is_active'] ?? false)) {
                    $permission['show_locked_routes'] = false;
                }
            }
        }

        $this->merge([
            'slug' => Str::slug((string) $this->input('name', '')),
            'permissions' => $permissions,
        ]);
    }

    public function rules(): array
    {
        return Role::rules($this);
    }

    public function messages(): array
    {
        return [
            'slug.required' => 'Informe um nome para o perfil.',
            'slug.unique' => 'Já existe um perfil com este nome.',
            'permissions.*.id.distinct' => 'Uma permissão não pode ser informada mais de uma vez.',
            'permissions.*.id.exists' => 'Uma das permissões informadas não está disponível.',
        ];
    }
}
