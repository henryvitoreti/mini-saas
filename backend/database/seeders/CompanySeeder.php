<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Repositories\RoleRepository;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $role = app(RoleRepository::class)->firstById(['id']);

        if (empty($role)) {
            throw new RuntimeException('Nenhum perfil de acesso central foi encontrado para vincular à empresa.');
        }

        DB::table('company')->updateOrInsert(
            ['id' => 1],
            [
                'role_id' => $role->id,
                'name' => 'Base',
                'document' => '11222333000181',
                'email' => 'base@workshop.local',
                'phone' => '48999999999',
                'secondary_phone' => null,
                'zip_code' => null,
                'street' => null,
                'number' => null,
                'complement' => null,
                'district' => null,
                'city' => null,
                'state' => null,
                'logo_path' => null,
                'notes' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        );
    }
}
