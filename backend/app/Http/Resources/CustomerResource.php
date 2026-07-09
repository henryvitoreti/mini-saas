<?php

namespace App\Http\Resources;

use App\Constants\RequestAttribute;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Customer $customer */
        $customer = $this->resource;

        if ((bool) $request->attributes->get(RequestAttribute::USE_SIMPLE_RESOURCE_RESPONSE, false)) {
            return $this->toSimpleArray($customer);
        }

        return $this->toDetailedArray($customer);
    }

    private function toSimpleArray(Customer $customer): array
    {
        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'document' => $customer->formatted_document,
            'email' => $customer->email,
            'type_label' => $customer->type?->getLabel(),
            'birth_date' => $customer->formatted_birth_date,
            'phone' => $customer->formatted_phone,
            'is_active' => $customer->is_active,
            'created_at' => $customer->formatted_created_at,
            'updated_at' => $customer->formatted_updated_at,
        ];
    }

    private function toDetailedArray(Customer $customer): array
    {
        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'document' => $customer->formatted_document,
            'document_raw' => $customer->document,
            'email' => $customer->email,
            'type' => $customer->type?->value,
            'type_label' => $customer->type?->getLabel(),
            'birth_date' => $customer->formatted_birth_date,
            'birth_date_raw' => $customer->birth_date?->format('Y-m-d'),
            'phone' => $customer->formatted_phone,
            'phone_raw' => $customer->phone,
            'secondary_phone' => $customer->formatted_secondary_phone,
            'secondary_phone_raw' => $customer->secondary_phone,
            'zip_code' => $customer->formatted_zip_code,
            'zip_code_raw' => $customer->zip_code,
            'street' => $customer->street,
            'number' => $customer->number,
            'complement' => $customer->complement,
            'district' => $customer->district,
            'city' => $customer->city,
            'state' => $customer->state,
            'notes' => $customer->notes,
            'is_active' => $customer->is_active,
            'created_at' => $customer->formatted_created_at,
            'updated_at' => $customer->formatted_updated_at,
        ];
    }
}
