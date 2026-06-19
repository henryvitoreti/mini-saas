<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permissions';

    protected $fillable = [
        'name',
        'slug',
        'base_front_url',
        'base_api_url',
        'show_locked_routes',
        'is_active'
    ];

    /**
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'show_locked_routes' => 'boolean',
            'is_active' => 'boolean'
        ];
    }
}
