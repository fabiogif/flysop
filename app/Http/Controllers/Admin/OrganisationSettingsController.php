<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Services\TenantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OrganisationSettingsController extends Controller
{
    public function __construct(
        protected TenantService $tenantService,
        protected AuditLogger $audit
    ) {
        $this->middleware(['can:settings']);
    }

    public function edit(): View
    {
        $tenant = Auth::user()->tenant;

        return view('admin.pages.settings.organisation', compact('tenant'));
    }

    public function update(Request $request): RedirectResponse
    {
        $tenant = Auth::user()->tenant;

        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'cnpj' => ['nullable', 'string', 'max:18'],
            'url' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            if ($tenant->logo && Storage::exists($tenant->logo)) {
                Storage::delete($tenant->logo);
            }
            $data['logo'] = $request->file('logo')->store('tenants');
        } else {
            unset($data['logo']);
        }

        $tenant->update($data);

        $this->audit->log('organisation.updated', $tenant, [
            'fields' => array_keys($data),
        ]);

        return back()->with('messageSuccess', 'Configurações da organização atualizadas.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $tenant = Auth::user()->tenant;

        $request->validate([
            'confirmation' => ['required', 'string'],
        ]);

        if (trim($request->input('confirmation')) !== $tenant->name) {
            return back()->with('messageDanger', 'Digite o nome exato da organização para confirmar a exclusão.');
        }

        $this->audit->log('organisation.deleted', $tenant, [
            'name' => $tenant->name,
            'tenant_id' => $tenant->id,
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $this->tenantService->deleteByAdmin($tenant->id);

        return redirect()->route('site.home')->with('messageSuccess', 'Organização excluída.');
    }
}
