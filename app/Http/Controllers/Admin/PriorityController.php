<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdatePriority;
use App\Models\Priority;
use Illuminate\Http\Request;

class PriorityController extends Controller
{
    protected $repository;

    public function __construct(Priority $priority)
    {
        $this->repository = $priority;
        $this->middleware(['can:priorities']);
    }

    public function index()
    {
        $priorities = $this->repository->orderBy('weight', 'desc')->paginate();

        return view('admin.pages.priorities.index', compact('priorities'));
    }

    public function create()
    {
        return view('admin.pages.priorities.create');
    }

    public function store(StoreUpdatePriority $request)
    {
        $this->repository->create($request->validated());

        return redirect()->route('priorities.index')->with('messageSuccess', 'Prioridade cadastrada com sucesso.');
    }

    public function show($id)
    {
        $priority = $this->repository->where('id', $id)->first();

        if (! $priority) {
            return redirect()->back();
        }

        return view('admin.pages.priorities.show', ['priority' => $priority]);
    }

    public function edit($id)
    {
        $priority = $this->repository->where('id', $id)->first();

        if (! $priority) {
            return redirect()->back();
        }

        return view('admin.pages.priorities.edit', ['priority' => $priority]);
    }

    public function update(StoreUpdatePriority $request, $id)
    {
        $priority = $this->repository->where('id', $id)->first();

        if (! $priority) {
            return redirect()->back();
        }

        $priority->update($request->validated());

        return redirect()->route('priorities.index')->with('messageSuccess', 'Prioridade atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $priority = $this->repository->where('id', $id)->first();

        if (! $priority) {
            return redirect()->back();
        }

        $priority->delete();

        return redirect()->route('priorities.index')->with('messageSuccess', 'Excluído com sucesso');
    }

    public function search(Request $request)
    {
        $filters = $request->all();
        $priorities = $this->repository->search($request->filter);

        return view('admin.pages.priorities.index', [
            'priorities' => $priorities,
            'filters' => $filters,
        ]);
    }
}
