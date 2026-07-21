<?php

namespace App\Http\Resources;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Company $company */
        $company = $this->resource;

        return [
            'id' => $company->id,
            'role_id' => $company->role_id,
            'name' => $company->name,
            'document' => $company->document,
            'email' => $company->email,
            'phone' => $company->phone,
            'secondary_phone' => $company->secondary_phone,
            'zip_code' => $company->zip_code,
            'street' => $company->street,
            'number' => $company->number,
            'complement' => $company->complement,
            'district' => $company->district,
            'city' => $company->city,
            'state' => $company->state,
            'logo_path' => $company->logo_path,
            'notes' => $company->notes,
            'role' => $this->formatRole($company),
            'permissions' => $company->role?->permissions?->toArray() ?? [],
        ];
    }

    private function formatRole(Company $company): array|null
    {
        return $company->role?->only([
            'id',
            'name',
            'slug',
            'description',
            'is_active',
        ]);
    }
}
