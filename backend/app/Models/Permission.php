<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class Permission extends Model
{
    use CentralConnection;

    protected $table = 'permissions';

    protected $fillable = [
        'name',
        'slug',
        'base_front_url',
        'base_api_url',
    ];

    /**
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'show_locked_routes' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
