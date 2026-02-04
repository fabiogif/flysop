<?php

namespace App\Http\Controllers\Admin\ACL;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateRole;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    protected $repository, $roles;

    public function __construct(Role $role)
    {
        $this->repository = $role;

        $this->middleware(['can:roles']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $roles = $this->repository->paginate();

        return view('admin.pages.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.pages.roles.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\StoreUpdateRole  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUpdateRole $request)
    {
        $this->repository->create($request->all());

        return redirect()->route('roles.index')->with('messageSuccess', 'Adicionado com sucesso');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $idRoles
     * @return \Illuminate\Http\Response
     */
    public function show($idRoles)
    {
        $roles = $this->repository->where('id', $idRoles)->first();

        if (!$roles) {
            return redirect()->back();
        }
        return  view('admin.pages.roles.show', ['role' => $roles]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($idRoles)
    {
        $roles = $this->repository->where('id', $idRoles)->first();

        if (!$roles) {
            return redirect()->back();
        }

        return  view('admin.pages.roles.edit', ['role' => $roles]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\StoreUpdateRole  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreUpdateRole $request, $idRole)
    {
        $role = $this->repository->where('id', $idRole)->first();

        if (!$role) {
            return redirect()->back();
        }

        $role->update($request->all());

        return  redirect()->route('roles.index')->with('messageSuccess', 'Alterado com sucesso');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($idRole)
    {
        $role = $this->repository->where('id', $idRole)->first();

        if (!$role) {
            return redirect()->back();
        }

        $role->delete();

        return  redirect()->route('roles.index')->with('messageSuccess', 'Excluido com sucesso');
    }


    public function search(Request $request)
    {

        $filters = $request->all();

        $role = $this->repository->where(function ($query) use ($request) {
            if ($request->filter) {
                $query->where('name', 'LIKE', "%{$request->filter}%")->orWhere('description', 'LIKE', "%{$request->filter}%");
            }
        })->paginate();

        return view(
            'admin.pages.roles.index',
            [
                'roles' => $role,
                'filters' => $filters
            ]
        );
    }
}
