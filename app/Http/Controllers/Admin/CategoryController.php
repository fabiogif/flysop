<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateCategory;
use App\Models\Category;
use Facade\FlareClient\View;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $repository;

    public function __construct(Category $category)
    {
        $this->repository = $category;
        $this->middleware(['can:categories']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = $this->repository->latest()->paginate();
        return view('admin.pages.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return View('admin.pages.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\StoreUpdateCategory  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUpdateCategory $request)
    {
        $this->repository->create($request->all());

        return redirect()->route('categories.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $category = $this->repository->find($id);

        if (!$category) {
            return redirect()->back();
        }
        return View('admin.pages.categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $category = $this->repository->where('id', $id)->first();

        if (!$category) {
            return redirect()->back();
        }
        return view(
            'admin.pages.categories.edit',
            compact('category')
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  App\Http\Requests\StoreUpdateCategory  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreUpdateCategory $request, $id)
    {
        $category = $this->repository->where('id', $id)->first();

        if (!$category) {
            return redirect()->back();
        }
        $category->update($request->all());

        return  redirect()->route('categories.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $category = $this->repository->where('id', $id)->first();

        if (!$category) {
            return redirect()->back();
        }

        $category->delete();

        return  redirect()->route('categories.index');
    }

    public function search(Request $request)
    {
        $filters = $request->all();
        $category = $this->repository->where(function ($query) use ($request) {
            if ($request->filter) {
                $query->orWhere('description', 'LIKE', "%{$request->filter}%");
                $query->orWhere('name', 'LIKE', "%{$request->filter}%");
            }
        })->latest()->paginate();

        return view(
            'admin.pages.categories.index',
            [
                'categories' => $category,
                'filters' => $filters
            ]
        );
    }
}
