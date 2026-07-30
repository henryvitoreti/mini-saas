<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insertOrIgnore([
            'id' => 1,
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Acesso total ao sistema.',
            'is_active' => true,
            'can_modify' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
