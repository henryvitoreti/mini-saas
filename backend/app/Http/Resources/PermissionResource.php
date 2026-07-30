<?php

namespace App\Http\Resources;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Permission $permission */
        $permission = $this->resource;
        $pivot = $permission->pivot;

        return [
            'id' => $permission->id,
            'name' => $permission->name,
            'slug' => $permission->slug,
            'base_front_url' => $permission->base_front_url,
            'base_api_url' => $permission->base_api_url,
            'group' => $permission->group,
            'is_base' => $permission->is_base,
            'show_locked_routes' => $pivot === null ? null : (bool) $pivot->show_locked_routes,
            'is_active' => $pivot === null ? null : (bool) $pivot->is_active,
        ];
    }
}
