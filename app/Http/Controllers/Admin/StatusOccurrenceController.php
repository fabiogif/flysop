<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StatusOccurrence;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUpdateStatusOccurrence;

class StatusOccurrenceController extends Controller
{

    protected $repository;

    public function __construct(StatusOccurrence $statusOccurrence)
    {
        $this->repository = $statusOccurrence;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $statusOccurrences = $this->repository->paginate();
        return view('admin.pages.statusOccurrences.index', compact('statusOccurrences'));
    }

    public function create()
    {
        return view('admin.pages.statusOccurrences.create');
    }

    public function store(StoreUpdateStatusOccurrence $request)
    {
        $this->repository->create($request->all());

        return redirect()->route('statusOccurrences.index');
    }

    public function show($id)
    {
        $statusOccurrences = $this->repository->where('id', $id)->first();

        if (!$statusOccurrences) {
            return redirect()->back();
        }

        return view('admin.pages.statusOccurrences.show', ['statusOccurrence' => $statusOccurrences]);
    }

    public function destroy($id)
    {
        $statusOccurrences = $this->repository->where('id', $id)->first();

        if (!$statusOccurrences) {
            return redirect()->back();
        }
        $statusOccurrences->delete();

        return redirect()->route('statusOccurrences.index')->with('messageSuccess', 'Excluido com sucesso');
    }


    public function search(Request $request)
    {

        $filters = $request->all();

        $statusOccurrences = $this->repository->search($request->filter);

        return view(
            'admin.pages.statusOccurrences.index',
        [
            'statusOccurrences' => $statusOccurrences,
            'filters' => $filters
        ]
        );
    }

    public function edit($id)
    {
        $statusOccurrences = $this->repository->where('id', $id)->first();

        if (!$statusOccurrences) {
            return redirect()->back();
        }
        return view(
            'admin.pages.statusOccurrences.edit',
        ['statusOccurrence' => $statusOccurrences]
        );
    }

    public function update(StoreUpdateStatusOccurrence $request, $id)
    {
        $statusOccurrences = $this->repository->where('id', $id)->first();

        if (!$statusOccurrences) {
            return redirect()->back();
        }
        $statusOccurrences->update($request->all());
        return redirect()->route('statusOccurrences.index');
    }
}
