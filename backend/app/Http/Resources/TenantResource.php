<?php

namespace App\Http\Resources;

use App\Constants\RequestAttribute;
use App\Models\Domain;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Tenant $tenant */
        $tenant = $this->resource;

        if ((bool) $request->attributes->get(RequestAttribute::USE_SIMPLE_RESOURCE_RESPONSE, false)) {
            return $this->toSimpleArray($tenant);
        }

        return $this->toDetailedArray($tenant);
    }

    private function toSimpleArray(Tenant $tenant): array
    {
        $data = is_array($tenant->data) ? $tenant->data : [];
        $domain = $tenant->domains->first();

        return [
            'id' => $tenant->id,
            'name' => $data['name'] ?? $tenant->id,
            'domain' => $domain instanceof Domain ? $domain->frontend_domain : null,
            'active' => $tenant->active,
            'created_at' => $tenant->created_at !== null ? $tenant->created_at->format('d/m/Y H:i') : null,
            'updated_at' => $tenant->updated_at !== null ? $tenant->updated_at->format('d/m/Y H:i') : null,
        ];
    }

    private function toDetailedArray(Tenant $tenant): array
    {
        $data = is_array($tenant->data) ? $tenant->data : [];
        $companyData = $tenant->getAttribute('company_data');
        $companyData = is_array($companyData) ? $companyData : [];
        $domain = $tenant->domains->first();

        return [
            'id' => $tenant->id,
            'name' => $companyData['name'] ?? $data['name'] ?? $tenant->id,
            'domain' => $domain instanceof Domain ? $domain->frontend_domain : null,
            'active' => $tenant->active,
            'role_id' => $companyData['role_id'] ?? null,
            'document' => $companyData['document'] ?? null,
            'email' => $companyData['email'] ?? null,
            'phone' => $companyData['phone'] ?? null,
            'secondary_phone' => $companyData['secondary_phone'] ?? null,
            'zip_code' => $companyData['zip_code'] ?? null,
            'street' => $companyData['street'] ?? null,
            'number' => $companyData['number'] ?? null,
            'complement' => $companyData['complement'] ?? null,
            'district' => $companyData['district'] ?? null,
            'city' => $companyData['city'] ?? null,
            'state' => $companyData['state'] ?? null,
            'logo_path' => $companyData['logo_path'] ?? null,
            'notes' => $companyData['notes'] ?? null,
            'created_at' => $tenant->created_at !== null ? $tenant->created_at->format('d/m/Y H:i') : null,
            'updated_at' => $tenant->updated_at !== null ? $tenant->updated_at->format('d/m/Y H:i') : null,
        ];
    }
}
