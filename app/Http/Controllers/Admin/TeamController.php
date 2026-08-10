<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateTeam;
use App\Models\Department;
use App\Models\Team;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    protected $repository;

    public function __construct(Team $team)
    {
        $this->repository = $team;
        $this->middleware(['can:teams']);
    }

    public function index()
    {
        $teams = $this->repository->with('department')->orderBy('name')->paginate();

        return view('admin.pages.teams.index', compact('teams'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();

        return view('admin.pages.teams.create', compact('departments'));
    }

    public function store(StoreUpdateTeam $request)
    {
        $this->repository->create($request->validated());

        return redirect()->route('teams.index')->with('messageSuccess', 'Equipe cadastrada com sucesso.');
    }

    public function show($id)
    {
        $team = $this->repository->with(['department', 'drivers'])->where('id', $id)->first();

        if (! $team) {
            return redirect()->back();
        }

        return view('admin.pages.teams.show', ['team' => $team]);
    }

    public function edit($id)
    {
        $team = $this->repository->where('id', $id)->first();

        if (! $team) {
            return redirect()->back();
        }

        $departments = Department::orderBy('name')->get();

        return view('admin.pages.teams.edit', ['team' => $team, 'departments' => $departments]);
    }

    public function update(StoreUpdateTeam $request, $id)
    {
        $team = $this->repository->where('id', $id)->first();

        if (! $team) {
            return redirect()->back();
        }

        $team->update($request->validated());

        return redirect()->route('teams.index')->with('messageSuccess', 'Equipe atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $team = $this->repository->where('id', $id)->first();

        if (! $team) {
            return redirect()->back();
        }

        $team->delete();

        return redirect()->route('teams.index')->with('messageSuccess', 'Excluído com sucesso');
    }

    public function search(Request $request)
    {
        $filters = $request->all();
        $teams = $this->repository->with('department')->search($request->filter);

        return view('admin.pages.teams.index', [
            'teams' => $teams,
            'filters' => $filters,
        ]);
    }
}
