<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use softDeletes;

    protected $table = 'roles';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active'
    ];

    /**
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'is_active' => 'boolean'
        ];
    }
}
