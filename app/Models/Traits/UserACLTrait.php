<?php

namespace App\Models\Traits;

use App\Models\Tenant;

trait UserACLTrait
{
    /**
     * Permissões do usuário (via cargos). Planos foram removidos; não usa mais permissionsPlan().
     */
    public function permissions(): array
    {
        return $this->permissionsRole();
    }

    /**
     * Permissões via plano (legado). Retorna vazio pois planos foram removidos.
     */
    public function permissionsPlan(): array
    {
        return [];
    }

    /**
     * Permissões via cargos do usuário (roles).
     */
    public function permissionsRole(): array
    {
        $roles = $this->roles()->with('permissions')->get();
        $permissions = [];
        foreach ($roles as $role) {
            foreach ($role->permissions as $permission) {
                $permissions[] = $permission->name;
            }
        }
        return array_unique($permissions);
    }

    public function hasPermission(string $permissionName): bool
    {
        return in_array($permissionName, $this->permissions());
    }

    /**
     * Verifica se o usuário possui o cargo (role) informado pelo nome.
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('roles.name', $roleName)->exists();
    }

    /**
     * Administrador da plataforma: hoje checa a role "Administrador" (Fase 2 do plano de execução).
     * A allowlist de e-mail em config('acl.admin') é mantida como fallback transitório — não remover
     * sem antes confirmar que todos os e-mails da allowlist têm a role atribuída em produção
     * (ver database/seeders/PermissionMenuSeeder.php).
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('Administrador') || in_array($this->email, config('acl.admin', []));
    }

    public function isNotAdmin(): bool
    {
        return !$this->isAdmin();
    }
}
