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

    public function isAdmin(): bool
    {
        return in_array($this->email, config('acl.admin', []));
    }

    public function isNotAdmin(): bool
    {
        return !$this->isAdmin();
    }
}
