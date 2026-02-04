<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TenantsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tenant::firstOrCreate(
            ['cnpj' => '18181223000168'],
            [
                'name' => 'Canecas Pontocom',
                'url' => 'canecas-pontocom',
                'uuid' => (string) Str::uuid(),
                'cpf' => '02530471533',
                'email' => 'fabiosantanagif@gmail.com',
                'subscription' => now(),
                'expires_at' => now()->addYear(),
            ]
        );
    }
}
