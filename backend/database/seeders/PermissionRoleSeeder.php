<?php

namespace Database\Seeders;

use App\Repositories\PermissionRepository;
use Illuminate\Database\Seeder;

class PermissionRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = app(PermissionRepository::class)->get();

        foreach ($permissions as $permission) {
            $permission->roles()->syncWithoutDetaching([
                1 => [
                    'show_locked_routes' => false,
                    'is_active' => true,
                ],
            ]);
        }
    }
}
