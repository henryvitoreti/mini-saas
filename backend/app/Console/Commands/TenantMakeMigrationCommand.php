<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TenantMakeMigrationCommand extends Command
{
    protected $signature = 'tenant:make-migration {name}';

    protected $description = 'Create a migration in the tenant migrations directory';

    public function handle(): int
    {
        $migrationName = $this->argument('name');

        $this->call('make:migration', [
            'name' => $migrationName,
            '--path' => 'database/migrations/tenant',
        ]);

        return self::SUCCESS;
    }
}
