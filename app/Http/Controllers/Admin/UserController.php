<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateUsers;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $repository, $users;

    public function __construct(User $users)
    {
        $this->repository = $users;
        $this->middleware(['can:users']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = $this->repository->latest()->tenantUser()->paginate();

        return view('admin.pages.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.pages.users.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\StoreUpdateUsers  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUpdateUsers $request)
    {

        $data = $request->all();
        $data['tenant_id'] = auth()->user()->tenant_id;
        $data['password'] = bcrypt($data['password']);
        $this->repository->create($data);

        return redirect()->route('users.index')->with('messageSuccess', 'Adicionado com sucesso');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $idUsers
     * @return \Illuminate\Http\Response
     */
    public function show($idUsers)
    {
        $user = $this->repository->tenantUser()->find($idUsers);

        if (!$user) {
            return redirect()->back();
        }
        return  view('admin.pages.users.show', ['user' => $user]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($idUsers)
    {
        $user = $this->repository->tenantUser()->where('id', $idUsers)->first();

        if (!$user) {
            return redirect()->back();
        }

        return  view('admin.pages.users.edit', ['user' => $user]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\StoreUpdateUsers  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreUpdateUsers $request, $idUsers)
    {
        $profile = $this->repository->tenantUser()->where('id', $idUsers)->first();

        if (!$profile) {
            return redirect()->back();
        }

        $data = $request->only(['name', 'email']);

        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }

        $profile->update($data);

        return  redirect()->route('users.index')->with('messageSuccess', 'Alterado com sucesso');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($idUsers)
    {
        $users = $this->repository->tenantUser()->where('id', $idUsers)->first();

        if (!$users) {
            return redirect()->back();
        }

        $users->delete();

        return  redirect()->route('users.index')->with('messageSuccess', 'Excluido com sucesso');
    }


    public function search(Request $request)
    {

        $filters = $request->all();

        $users = $this->repository->where(function ($query) use ($request) {

            if ($request->filter) {
                $query->orWhere('name', 'LIKE', "%{$request->filter}%");
                $query->orWhere('email', "$request->filter");
            }
        })->latest()->tenantUser()->paginate();

        return view(
            'admin.pages.users.index',
            [
                'users' => $users,
                'filters' => $filters
            ]
        );
    }
}
