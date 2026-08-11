<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Legado: resquício de um modelo antigo de "planos" (permissionsPlan(), removida de
 * UserACLTrait). Não está ligado a User hoje — ACL real é User<->Role<->Permission
 * (ver app/Policies/OccurrencePolicy.php e RoleSeeder). Não reativar nem misturar com
 * Policies novas; candidato a remoção quando as telas admin/profiles forem descontinuadas.
 */
class Profile extends Model
{
    protected $fillable = ['name', 'description'];
    use HasFactory;

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }


    public function permissionsAvailable($filter = null)
    {
        $permissions = Permission::whereNotIn('permissions.id', function ($query) {
            $query->select('permission_profile.permission_id');
            $query->from('permission_profile');
            $query->whereRaw("permission_profile.profile_id={$this->id}");
        })->where(function ($queryFilter) use ($filter) {
            if ($filter) {
                $queryFilter->where('permissions.name', 'LIKE', "%{$filter}%");
            }
        })->paginate();

        return $permissions;
    }
}
