<?php

namespace App\Console\Commands;

use Database\Seeders\PermissionRoleSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Console\Command;

class PermissionUpdateCommand extends Command
{
    protected $signature = 'permission:update {--force : Force the operation to run when in production}';

    protected $description = 'Run permission and permission_role seeders';

    public function handle(): int
    {
        $this->call('db:seed', [
            '--class' => PermissionSeeder::class,
            '--force' => $this->option('force'),
        ]);

        return $this->call('db:seed', [
            '--class' => PermissionRoleSeeder::class,
            '--force' => $this->option('force'),
        ]);
    }
}
