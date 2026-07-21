<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class Role extends Model
{
    use CentralConnection;
    use SoftDeletes;

    protected $table = 'roles';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    /**
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role', 'role_id', 'permission_id')
            ->select([
                'permissions.id',
                'permissions.name',
                'permissions.slug',
                'permissions.base_front_url',
                'permissions.base_api_url',
                'permission_role.show_locked_routes',
                'permission_role.is_active',
            ])
            ->withPivot(['show_locked_routes', 'is_active']);
    }

    public function getPermissionIds(): Collection
    {
        return $this->permissions()->select('id')->get();
    }
}
