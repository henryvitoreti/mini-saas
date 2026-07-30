<?php

namespace Database\Seeders;

use App\Repositories\PermissionRepository;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    private const array PERMISSIONS = [
        [
            'name' => 'Clientes',
            'slug' => 'customers',
            'base_api_url' => 'api/customers',
            'base_front_url' => 'clientes',
            'group' => 'Cadastros',
            'is_base' => true,
        ],
    ];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $permission) {
            app(PermissionRepository::class)->updateOrCreate([
                'slug' => $permission['slug'],
            ], $permission);
        }
    }
}
