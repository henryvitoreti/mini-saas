<?php

namespace App\Console\Commands;

use Database\Seeders\DatabaseTenantSeeder;
use Illuminate\Console\Command;

class TenantSeedCommand extends Command
{
    protected $signature = 'tenant:seed {--force : Force the operation to run when in production}';

    protected $description = 'Run tenant seeders for all existing tenants';

    public function handle(): int
    {
        return $this->call('tenants:seed', [
            '--class' => DatabaseTenantSeeder::class,
            '--force' => $this->option('force'),
        ]);
    }
}
