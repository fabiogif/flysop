<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateTenant;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class TenantController extends Controller
{
    public function __construct(
        protected TenantService $tenantService
    ) {
        $this->middleware(['can:tenants']);
    }

    public function index(): View
    {
        $tenants = $this->tenantService->getPaginatedList();

        return view('admin.pages.tenants.index', compact('tenants'));
    }

    public function create(): View
    {
        return view('admin.pages.tenants.create');
    }

    public function store(StoreUpdateTenant $request): RedirectResponse
    {
        $this->tenantService->createByAdmin($request->validated(), $request->file('logo'));

        return redirect()->route('tenants.index');
    }

    public function show(int $id): View|RedirectResponse
    {
        $tenant = $this->tenantService->findOrFail($id);

        return view('admin.pages.tenants.show', compact('tenant'));
    }

    public function edit(int $id): View|RedirectResponse
    {
        $tenant = $this->tenantService->findOrFail($id);

        return view('admin.pages.tenants.edit', compact('tenant'));
    }

    public function update(StoreUpdateTenant $request, int $id): RedirectResponse
    {
        $this->tenantService->updateByAdmin($id, $request->validated(), $request->file('logo'));

        return redirect()->route('tenants.index');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->tenantService->deleteByAdmin($id);

        return redirect()->route('tenants.index');
    }

    public function search(Request $request): View
    {
        $filters = $request->all();
        $tenants = $this->tenantService->search($request->get('filter'));

        return view('admin.pages.tenants.index', [
            'tenants' => $tenants,
            'filters' => $filters,
        ]);
    }
}
