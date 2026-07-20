<?php

namespace App\Http\Resources;

use App\Helpers\CompanyHelper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var User $user */
        $user = $this->resource;
        $company = CompanyHelper::getCurrentCompanyResource();

        return [
            'id' => $user->id,
            'role_id' => $company['role_id'] ?? null,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'is_active' => $user->is_active,
            'permissions' => $company['permissions'] ?? [],
            'company' => $company,
        ];
    }
}
