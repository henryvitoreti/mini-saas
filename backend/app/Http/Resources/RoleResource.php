<?php

namespace App\Http\Resources;

use App\Constants\RequestAttribute;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Role $role */
        $role = $this->resource;

        if ($request->attributes->get(RequestAttribute::USE_SIMPLE_RESOURCE_RESPONSE, false)) {
            return $this->toSimpleArray($role);
        }

        return $this->toDetailedArray($role, $request);
    }

    private function toSimpleArray(Role $role): array
    {
        return [
            'id' => $role->id,
            'name' => $role->name,
            'slug' => $role->slug,
            'description' => $role->description,
            'is_active' => $role->is_active,
            'can_modify' => $role->can_modify,
            'created_at' => $role->created_at?->format('d/m/Y H:i'),
            'updated_at' => $role->updated_at?->format('d/m/Y H:i'),
        ];
    }

    private function toDetailedArray(Role $role, Request $request): array
    {
        $response = $this->toSimpleArray($role);

        if ($role->relationLoaded('permissions')) {
            $response['permissions'] = PermissionResource::collection($role->permissions)->resolve($request);
        }

        return $response;
    }
}
