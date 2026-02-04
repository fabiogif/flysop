<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TypeOccurrence;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUpdateTypeOccurrence;

class TypeOccurrenceController extends Controller
{

    protected $repository;

    public function __construct(TypeOccurrence $typeOccurrence)
    {
        $this->repository = $typeOccurrence;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $typeOccurrences = $this->repository->paginate();
        return view('admin.pages.typeOccurrences.index', compact('typeOccurrences'));
    }

    public function create()
    {
        return view('admin.pages.typeOccurrences.create');
    }

    public function store(StoreUpdateTypeOccurrence $request)
    {
        $this->repository->create($request->all());

        return redirect()->route('typeOccurrences.index');
    }

    public function show($id)
    {
        $typeOccurrences = $this->repository->where('id', $id)->first();

        if (!$typeOccurrences) {
            return redirect()->back();
        }

        return  view('admin.pages.typeOccurrences.show', ['typeOccurrence' => $typeOccurrences]);
    }

    public function destroy($id)
    {
        $typeOccurrences = $this->repository->where('id', $id)->first();

        if (!$typeOccurrences) {
            return redirect()->back();
        }
        $typeOccurrences->delete();

        return  redirect()->route('typeOccurrences.index')->with('messageSuccess', 'Excluido com sucesso');
    }


    public function search(Request $request)
    {

        $filters = $request->all();

        $typeOccurrence = $this->repository->search($request->filter);

        return view(
            'admin.pages.typeOccurrences.index',
            [
                'typeOccurrences' => $typeOccurrence,
                'filters' => $filters
            ]
        );
    }

    public function edit($id)
    {
        $typeOccurrences = $this->repository->where('id', $id)->first();

        if (!$typeOccurrences) {
            return redirect()->back();
        }
        return view(
            'admin.pages.typeOccurrences.edit',
            ['typeOccurrence' => $typeOccurrences]
        );
    }

    public function update(StoreUpdateTypeOccurrence $request, $id)
    {
        $typeOccurrences = $this->repository->where('id', $id)->first();

        if (!$typeOccurrences) {
            return redirect()->back();
        }
        $typeOccurrences->update($request->all());
        return  redirect()->route('typeOccurrences.index');
    }
}
