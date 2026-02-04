<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Ordem: tenants e usuários primeiro; permissões; catálogos (tipos, status, órgãos);
     * clientes; ocorrências (dependem de clientes e catálogos).
     */
    public function run(): void
    {
        $this->call([
            TenantsTableSeeder::class,
            AdminUserSeeder::class,
            PermissionMenuSeeder::class,
            RoleSeeder::class,
            TypeOccurrenceTableSeeder::class,
            StatusOccurrenceTableSeeder::class,
            StatusOccurrenceDriverSeeder::class,
            IssuingsTableSeeder::class,
            ClientTableSeeder::class,
            OccurrenceTableSeeder::class,
        ]);
    }
}
