<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Cria os cargos (roles) do sistema: Administrador, Secretaria, Motorista, Operador,
 * Atendente, Supervisor. Define as permissões apropriadas para cada cargo.
 *
 * "Motorista" cobre o conceito de "Agente de Campo" da especificação original —
 * não existe role separada (decisão da Fase 2 do plano de execução, para reaproveitar
 * a tabela drivers, o middleware ensure.driver e o painel já existentes).
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
                    'settings',
                    'audit',
                    'statusOccurrences',
                    'typeOccurrences',
                    'priorities',
                    'departments',
                    'teams',
                    'issuings',
                    'occurrences',
                    'drivers',
                ],
            ],
            [
                'name' => 'Secretaria',
                'description' => 'Acesso para gerenciar ocorrências, tipos, status, prioridades e órgãos',
                'permissions' => [
                    'statusOccurrences',
                    'typeOccurrences',
                    'priorities',
                    'issuings',
                    'occurrences',
                    'drivers',
                ],
            ],
            [
                'name' => 'Motorista',
                'description' => 'Acesso ao painel do motorista (agente de campo) para gerenciar ocorrências atribuídas',
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
            [
                'name' => 'Atendente',
                'description' => 'Registra e triagem ocorrências: cadastra, edita, classifica prioridade e encaminha',
                'permissions' => [
                    'occurrences',
                ],
            ],
            [
                'name' => 'Supervisor',
                'description' => 'Distribui ocorrências entre equipes, altera prioridades, acompanha SLA e reabre ocorrências',
                'permissions' => [
                    'occurrences',
                    'statusOccurrences',
                    'typeOccurrences',
                    'priorities',
                    'issuings',
                    'drivers',
                    'departments',
                    'teams',
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
