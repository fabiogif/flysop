<?php

namespace App\Http\Controllers\Admin\ACL;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUpdateProfile;

class ProfileController extends Controller
{
    protected $repository, $profiles;

    public function __construct(Profile $profile)
    {
        $this->repository = $profile;

        $this->middleware(['can:profiles']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $profiles = $this->repository->paginate();
        return view('admin.pages.profiles.index', compact('profiles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.pages.profiles.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\StoreUpdateProfile  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUpdateProfile $request)
    {
        $this->repository->create($request->all());

        return redirect()->route('profiles.index')->with('messageSuccess', 'Adicionado com sucesso');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $idProfiles
     * @return \Illuminate\Http\Response
     */
    public function show($idProfiles)
    {
        $profiles = $this->repository->where('id', $idProfiles)->first();

        if (!$profiles) {
            return redirect()->back();
        }
        return  view('admin.pages.profiles.show', ['profile' => $profiles]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($idProfiles)
    {
        $profiles = $this->repository->where('id', $idProfiles)->first();

        if (!$profiles) {
            return redirect()->back();
        }

        return  view('admin.pages.profiles.edit', ['profile' => $profiles]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\StoreUpdateProfile  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreUpdateProfile $request, $idProfile)
    {
        $profile = $this->repository->where('id', $idProfile)->first();

        if (!$profile) {
            return redirect()->back();
        }

        $profile->update($request->all());

        return  redirect()->route('profiles.index')->with('messageSuccess', 'Alterado com sucesso');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($idProfile)
    {
        $profile = $this->repository->where('id', $idProfile)->first();

        if (!$profile) {
            return redirect()->back();
        }

        $profile->delete();

        return  redirect()->route('profiles.index')->with('messageSuccess', 'Excluido com sucesso');
    }


    public function search(Request $request)
    {

        $filters = $request->all();

        $profile = $this->repository->where(function ($query) use ($request) {
            if ($request->filter) {
                $query->where('name', $request->filter)->orWhere('description', 'LIKE', "%{$request->filter}%");
            }
        })->paginate();

        return view(
            'admin.pages.profiles.index',
            [
                'profiles' => $profile,
                'filters' => $filters
            ]
        );
    }
}
