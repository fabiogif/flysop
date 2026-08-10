<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Cria permissões do menu admin e cargo Administrador com todas as permissões.
 * Atribui o cargo a todos os usuários cujo e-mail está em config('acl.admin') —
 * mesma lista usada por User::isAdmin() como fallback (ver UserACLTrait).
 *
 * Permissões devem coincidir com o 'can' do menu em config/adminlte.php.
 */
class PermissionMenuSeeder extends Seeder
{
    /** Nomes das permissões usadas no menu (config/adminlte.php). */
    protected array $menuPermissions = [
        'tenants' => 'Empresas',
        'profiles' => 'Perfis',
        'roles' => 'Cargos',
        'permissions' => 'Permissões',
        'users' => 'Usuários',
        'statusOccurrences' => 'Status de Ocorrências',
        'typeOccurrences' => 'Tipo de Ocorrências',
        'priorities' => 'Prioridades',
        'departments' => 'Departamentos',
        'teams' => 'Equipes',
        'issuings' => 'Órgãos',
        'occurrences' => 'Ocorrências',
        'drivers' => 'Motoristas',
    ];

    public function run(): void
    {
        $permissions = [];
        foreach ($this->menuPermissions as $name => $description) {
            $permissions[] = Permission::firstOrCreate(
                ['name' => $name],
                ['description' => $description]
            );
        }

        $role = Role::firstOrCreate(
            ['name' => 'Administrador'],
            ['description' => 'Acesso total ao sistema']
        );
        $role->permissions()->sync(collect($permissions)->pluck('id'));

        $adminEmails = config('acl.admin', []);
        $users = User::whereIn('email', $adminEmails)->get();
        foreach ($users as $user) {
            if (!$user->roles()->where('roles.id', $role->id)->exists()) {
                $user->roles()->attach($role->id);
            }
        }
    }
}
