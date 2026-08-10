<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateDriver;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class DriverController extends Controller
{
    public function __construct(protected Driver $repository)
    {
        $this->middleware(['can:drivers']);
    }

    protected function scopeTenant()
    {
        return $this->repository->where('tenant_id', auth()->user()->tenant_id);
    }

    public function index(): View
    {
        $drivers = $this->scopeTenant()->latest()->paginate(15);

        return view('admin.pages.drivers.index', compact('drivers'));
    }

    public function create(): View
    {
        $driver = new Driver;

        return view('admin.pages.drivers.create', compact('driver'));
    }

    public function store(StoreUpdateDriver $request): RedirectResponse
    {
        $data = $request->validated();
        $data['tenant_id'] = auth()->user()->tenant_id;
        Driver::create($data);

        return redirect()->route('drivers.index')->with('messageSuccessDriver', 'Motorista cadastrado com sucesso.');
    }

    public function show(int $id): View|RedirectResponse
    {
        $driver = $this->scopeTenant()->findOrFail($id);
        $driver->load('occurrences');

        return view('admin.pages.drivers.show', compact('driver'));
    }

    public function edit(int $id): View|RedirectResponse
    {
        $driver = $this->scopeTenant()->findOrFail($id);

        return view('admin.pages.drivers.edit', compact('driver'));
    }

    public function update(StoreUpdateDriver $request, int $id): RedirectResponse
    {
        $driver = $this->scopeTenant()->findOrFail($id);
        $driver->update($request->validated());

        return redirect()->route('drivers.index')->with('messageSuccessDriver', 'Motorista atualizado com sucesso.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $driver = $this->scopeTenant()->findOrFail($id);
        $driver->occurrences()->update(['driver_id' => null]);
        $driver->delete();

        return redirect()->route('drivers.index')->with('messageSuccessDriver', 'Motorista excluído com sucesso.');
    }

    public function search(Request $request): View
    {
        $filters = $request->all();
        $drivers = $this->scopeTenant()
            ->when($request->get('filter'), function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->filter . '%')
                    ->orWhere('email', 'like', '%' . $request->filter . '%');
            })
            ->latest()
            ->paginate(15);

        return view('admin.pages.drivers.index', [
            'drivers' => $drivers,
            'filters' => $filters,
        ]);
    }
}
