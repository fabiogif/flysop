<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Cria os cargos (roles) do sistema: Administrador, Secretaria, Motorista, Operador.
 * Define as permissões apropriadas para cada cargo.
 */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Administrador',
                'description' => 'Acesso total ao sistema',
                'permissions' => [
                    'tenants',
                    'profiles',
                    'roles',
                    'permissions',
                    'users',
                    'statusOccurrences',
                    'typeOccurrences',
                    'issuings',
                    'occurrences',
                    'drivers',
                ],
            ],
            [
                'name' => 'Secretaria',
                'description' => 'Acesso para gerenciar ocorrências, tipos, status e órgãos',
                'permissions' => [
                    'statusOccurrences',
                    'typeOccurrences',
                    'issuings',
                    'occurrences',
                    'drivers',
                ],
            ],
            [
                'name' => 'Motorista',
                'description' => 'Acesso ao painel do motorista para gerenciar ocorrências atribuídas',
                'permissions' => [
                    'occurrences',
                ],
            ],
            [
                'name' => 'Operador',
                'description' => 'Acesso para visualizar e atualizar ocorrências',
                'permissions' => [
                    'occurrences',
                    'statusOccurrences',
                    'typeOccurrences',
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(
                ['name' => $roleData['name']],
                ['description' => $roleData['description']]
            );

            $permissionIds = [];
            foreach ($roleData['permissions'] as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission) {
                    $permissionIds[] = $permission->id;
                }
            }

            if (!empty($permissionIds)) {
                $role->permissions()->sync($permissionIds);
            }
        }
    }
}
