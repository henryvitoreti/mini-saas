<?php

namespace App\Models;

use App\Helpers\ConnectionHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
        'can_modify',
    ];

    /**
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'is_active' => 'boolean',
            'can_modify' => 'boolean',
        ];
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role', 'role_id', 'permission_id')
            ->select([
                'permissions.id',
                'permissions.name',
                'permissions.slug',
                'permissions.group',
                'permissions.is_base',
                'permissions.base_front_url',
                'permissions.base_api_url',
            ])
            ->withPivot(['show_locked_routes', 'is_active'])
            ->orderBy('permissions.group')
            ->orderBy('permissions.name');
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'role_id');
    }

    public static function rules(Request $request): array
    {
        $id = $request->route('id');

        $uniqueSlugRule = Rule::unique(ConnectionHelper::centralTable('roles'), 'slug')
            ->when($id !== null, fn ($query) => $query->ignore($id))
            ->whereNull('deleted_at');

        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:255', $uniqueSlugRule],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
            'permissions' => ['present', 'array'],
            'permissions.*' => ['array:id,is_active,show_locked_routes'],
            'permissions.*.id' => [
                'required',
                'integer',
                'distinct:strict',
                Rule::exists(ConnectionHelper::centralTable('permissions'), 'id'),
            ],
            'permissions.*.is_active' => ['required', 'boolean'],
            'permissions.*.show_locked_routes' => ['required', 'boolean'],
        ];
    }
}
