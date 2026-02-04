<?php

namespace App\Http\Controllers\Admin\ACL;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;

class PermissionRoleController extends Controller
{
    protected $repository, $role, $permission;

    public function __construct(Role $role, Permission $permission)
    {
        $this->role = $role;
        $this->permission = $permission;
        $this->middleware(['can:roles']);
    }



    public function permissions($idRole)
    {
        $role = $this->role->find($idRole);

        if (!$role) {
            return redirect()->back();
        }
        $permissions = $role->permissions()->paginate();

        return view('admin.pages.roles.permissions.index', compact('role', 'permissions'));
    }

    public function roles($idPermission)
    {
        $permission = $this->permission->find($idPermission);

        if (!$permission) {
            return redirect()->back();
        }
        $roles = $permission->roles()->paginate();

        return view('admin.pages.permission.roles.roles', compact('roles', 'permission'));
    }

    public function permissionsAvailable(Request $request, $idRole)
    {

        $role = $this->role->find($idRole);

        if (!$role) {
            return redirect()->back();
        }

        $filters = $request->except('_token');

        $permissions = $role->permissionsAvailable($request->filter);

        return view('admin.pages.roles.permissions.available', compact('role', 'permissions', 'filters'));
    }

    public function attachPermissionRole(Request $request, $idRole)
    {
        $role = $this->role->find($idRole);

        if (!$role) {
            return redirect()->back();
        }

        if (!$request->permissions || count($request->permissions) == 0) {
            return redirect()->back()->with('messageWarning', 'Precisa escolher pelo menos uma permissão');
        }



        $role->permissions()->attach($request->permissions);

        return redirect()->route('roles.permissions', $role->id)->with('messageSuccess', 'Vinculado com sucesso');
    }

    public function detachPermissionRole($idRole, $idPermission)
    {
        $role = $this->role->find($idRole);
        $permission = $this->permission->find($idPermission);

        if (!$role || !$permission) {
            return redirect()->back()->with('messageDanger', 'Não encontrado');
        }

        $role->permissions()->detach($permission);
        return redirect()->route('roles.permissions', $role->id)->with('messageSuccess', 'Desvinculado com sucesso');
    }
}
