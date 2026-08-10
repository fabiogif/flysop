<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateDepartment;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    protected $repository;

    public function __construct(Department $department)
    {
        $this->repository = $department;
        $this->middleware(['can:departments']);
    }

    public function index()
    {
        $departments = $this->repository->orderBy('name')->paginate();

        return view('admin.pages.departments.index', compact('departments'));
    }

    public function create()
    {
        return view('admin.pages.departments.create');
    }

    public function store(StoreUpdateDepartment $request)
    {
        $this->repository->create($request->validated());

        return redirect()->route('departments.index')->with('messageSuccess', 'Departamento cadastrado com sucesso.');
    }

    public function show($id)
    {
        $department = $this->repository->with('teams')->where('id', $id)->first();

        if (! $department) {
            return redirect()->back();
        }

        return view('admin.pages.departments.show', ['department' => $department]);
    }

    public function edit($id)
    {
        $department = $this->repository->where('id', $id)->first();

        if (! $department) {
            return redirect()->back();
        }

        return view('admin.pages.departments.edit', ['department' => $department]);
    }

    public function update(StoreUpdateDepartment $request, $id)
    {
        $department = $this->repository->where('id', $id)->first();

        if (! $department) {
            return redirect()->back();
        }

        $department->update($request->validated());

        return redirect()->route('departments.index')->with('messageSuccess', 'Departamento atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $department = $this->repository->where('id', $id)->first();

        if (! $department) {
            return redirect()->back();
        }

        $department->delete();

        return redirect()->route('departments.index')->with('messageSuccess', 'Excluído com sucesso');
    }

    public function search(Request $request)
    {
        $filters = $request->all();
        $departments = $this->repository->search($request->filter);

        return view('admin.pages.departments.index', [
            'departments' => $departments,
            'filters' => $filters,
        ]);
    }
}
