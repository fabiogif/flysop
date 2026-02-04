<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Cria tenant e usuários de acesso (fabio e robson).
     */
    public function run(): void
    {
        $tenant = Tenant::first();

        if (!$tenant) {
            $tenant = Tenant::create([
                'name' => 'SOP Admin',
                'url' => 'sop-admin',
                'uuid' => (string) Str::uuid(),
                'cnpj' => '00000000000000',
                'cpf' => '00000000000',
                'email' => 'admin@sop.local',
                'active' => '1',
                'subscription' => now(),
                'expires_at' => now()->addYear(),
            ]);
        }

        $users = [
            ['name' => 'Fabio', 'email' => 'fabio@fabio.com', 'password' => '123456ab'],
            ['name' => 'Robson', 'email' => 'robson@robson.com', 'password' => '123456ab'],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => bcrypt($data['password']),
                    'tenant_id' => $tenant->id,
                ]
            );
        }
    }
}
