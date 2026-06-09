<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BaseTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenant = Tenant::query()->firstOrCreate(
            [
                'id' => 'base',
            ],
            [
                'active' => true,
            ],
        );

        $tenant->domains()->firstOrCreate([
            'domain' => 'api.app.127.0.0.1.sslip.io',
        ]);
    }
}
