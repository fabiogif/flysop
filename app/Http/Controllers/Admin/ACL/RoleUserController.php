<?php

namespace App\Http\Controllers\Admin\ACL;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class RoleUserController extends Controller
{
    protected $repository, $user, $role;

    public function __construct(User $user, Role $role)
    {
        $this->user = $user;
        $this->role = $role;
        $this->middleware(['can:users']);
    }



    public function roles($idUser)
    {
        $user = $this->user->find($idUser);

        if (!$user) {
            return redirect()->back();
        }
        $roles = $user->roles()->paginate();

        return view('admin.pages.users.roles.roles', compact('user', 'roles'));
    }

    public function user($idRole)
    {
        $role = $this->role->find($idRole);

        if (!$role) {
            return redirect()->back();
        }
        $user = $role->user()->paginate();

        return view('admin.pages.roles.users.users', compact('role', 'users'));
    }

    public function rolesAvailable(Request $request, $idUser)
    {

        $user = $this->user->find($idUser);

        if (!$user) {
            return redirect()->back();
        }

        $filters = $request->except('_token');

        $roles = $user->rolesAvailable($request->filter);

        return view('admin.pages.users.roles.available', compact('user', 'roles', 'filters'));
    }

    public function attachRolesUser(Request $request, $idUser)
    {
        $user = $this->user->find($idUser);

        if (!$user) {
            return redirect()->back();
        }

        if (!$request->roles || count($request->roles) == 0) {
            return redirect()->back()->with('messageWarning', 'Precisa escolher pelo menos uma permissão');
        }

        $user->roles()->attach($request->roles);

        return redirect()->route('users.roles', $user->id)->with('messageSuccess', 'Vinculado com sucesso');
    }

    public function detachRoleUser($idUser, $idRole)
    {
        $user = $this->user->find($idUser);
        $role = $this->role->find($idRole);

        if (!$user || !$role) {
            return redirect()->back()->with('messageDanger', 'Não encontrado');
        }

        $user->roles()->detach($role);
        return redirect()->route('users.roles', $user->id)->with('messageSuccess', 'Desvinculado com sucesso');
    }
}
