<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseTenantSeeder extends Seeder
{
    /**
     * Seed the tenant databases.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            CustomerSeeder::class,
        ]);
    }
}
