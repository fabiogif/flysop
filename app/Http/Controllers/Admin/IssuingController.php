<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Issuing;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUpdateIssuing;

class IssuingController extends Controller
{

    protected $repository;

    public function __construct(Issuing $issuing)
    {
        $this->repository = $issuing;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $issuings = $this->repository->paginate();
        return view('admin.pages.issuings.index', compact('issuings'));
    }

    public function create()
    {
        return view('admin.pages.issuings.create');
    }

    public function store(StoreUpdateIssuing $request)
    {
        $this->repository->create($request->all());

        return redirect()->route('issuings.index');
    }

    public function show($id)
    {
        $issuings = $this->repository->where('id', $id)->first();

        if (!$issuings) {
            return redirect()->back();
        }

        return  view('admin.pages.issuings.show', ['issuing' => $issuings]);
    }

    public function destroy($id)
    {
        $issuings = $this->repository->where('id', $id)->first();

        if (!$issuings) {
            return redirect()->back();
        }
        $issuings->delete();

        return  redirect()->route('issuings.index')->with('messageSuccess', 'Excluido com sucesso');
    }


    public function search(Request $request)
    {

        $filters = $request->all();

        $issuing = $this->repository->search($request->filter);

        return view(
            'admin.pages.issuings.index',
            [
                'issuings' => $issuing,
                'filters' => $filters
            ]
        );
    }

    public function edit($id)
    {
        $issuing = $this->repository->where('id', $id)->first();

        if (!$issuing) {
            return redirect()->back();
        }
        return view(
            'admin.pages.issuings.edit',
            ['issuing' => $issuing]
        );
    }

    public function update(StoreUpdateIssuing $request, $id)
    {
        $issuings = $this->repository->where('id', $id)->first();

        if (!$issuings) {
            return redirect()->back();
        }
        $issuings->update($request->all());
        return  redirect()->route('issuings.index');
    }
}
