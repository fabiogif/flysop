<?php

namespace App\Http\Controllers\Admin\ACL;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUpdatePermission;

class PermissionController extends Controller
{
    protected $repository, $permission;

    public function __construct(Permission $permission)
    {
        $this->repository = $permission;
        $this->middleware(['can:permissions']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $permissions = $this->repository->paginate();
        return view('admin.pages.permission.index', compact('permissions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.pages.permission.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\StoreUpdatePermission  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUpdatePermission $request)
    {
        $this->repository->create($request->all());

        return redirect()->route('permission.index')->with('messageSuccess', 'Adicionado com sucesso');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $idPermissions
     * @return \Illuminate\Http\Response
     */
    public function show($idPermissions)
    {
        $permission = $this->repository->where('id', $idPermissions)->first();

        if (!$permission) {
            return redirect()->back();
        }
        return  view('admin.pages.permission.show', ['permission' => $permission]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($idPermissions)
    {
        $permission = $this->repository->where('id', $idPermissions)->first();

        if (!$permission) {
            return redirect()->back();
        }

        return  view('admin.pages.permission.edit', ['permission' => $permission]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\StoreUpdatePermission  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreUpdatePermission $request, $idPermission)
    {
        $permission = $this->repository->where('id', $idPermission)->first();

        if (!$permission) {
            return redirect()->back();
        }

        $permission->update($request->all());

        return  redirect()->route('permission.index')->with('messageSuccess', 'Alterado com sucesso');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($idPermission)
    {
        $permission = $this->repository->where('id', $idPermission)->first();

        if (!$permission) {
            return redirect()->back();
        }

        $permission->delete();

        return  redirect()->route('permission.index')->with('messageSuccess', 'Excluido com sucesso');
    }


    public function search(Request $request)
    {

        $filters = $request->all();

        $permissions = $this->repository->where(function ($query) use ($request) {
            if ($request->filter) {
                $query->where('name', $request->filter)->orWhere('description', 'LIKE', "%{$request->filter}%");
            }
        })->paginate();

        return view(
            'admin.pages.permission.index',
            [
                'permissions' => $permissions,
                'filters' => $filters
            ]
        );
    }
}
