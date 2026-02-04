<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateTable;
use App\Models\Table;
use Illuminate\Http\Request;

class TableController extends Controller
{
    protected $repository;

    public function __construct(Table $table)
    {
        $this->repository = $table;
        $this->middleware(['can:tables']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tables = $this->repository->latest()->paginate();
        return view('admin.pages.tables.index', compact('tables'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return View('admin.pages.tables.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\StoreUpdateTable  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUpdateTable $request)
    {
        $this->repository->create($request->all());

        return redirect()->route('tables.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $table = $this->repository->find($id);

        if (!$table) {
            return redirect()->back();
        }
        return View('admin.pages.tables.show', compact('table'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $table = $this->repository->where('id', $id)->first();

        if (!$table) {
            return redirect()->back();
        }
        return view(
            'admin.pages.tables.edit',
            compact('table')
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  App\Http\Requests\StoreUpdateTable  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreUpdateTable $request, $id)
    {
        $table = $this->repository->where('id', $id)->first();

        if (!$table) {
            return redirect()->back();
        }
        $table->update($request->all());

        return  redirect()->route('tables.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $table = $this->repository->where('id', $id)->first();

        if (!$table) {
            return redirect()->back();
        }

        $table->delete();

        return  redirect()->route('tables.index');
    }

    public function search(Request $request)
    {
        $filters = $request->all();
        $table = $this->repository->where(function ($query) use ($request) {
            if ($request->filter) {
                $query->orWhere('description', 'LIKE', "%{$request->filter}%");
                $query->orWhere('name', 'LIKE', "%{$request->filter}%");
            }
        })->latest()->paginate();

        return view(
            'admin.pages.tables.index',
            [
                'tables' => $table,
                'filters' => $filters
            ]
        );
    }
}
