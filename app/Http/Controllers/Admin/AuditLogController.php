<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(['can:audit']);
    }

    public function index(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;
        $userIds = User::where('tenant_id', $tenantId)->pluck('id');

        $activities = Activity::query()
            ->with(['causer', 'subject'])
            ->where(function ($query) use ($tenantId, $userIds) {
                $query->where('properties->tenant_id', $tenantId)
                    ->orWhereIn('causer_id', $userIds);
            })
            ->when($request->filled('filter'), function ($query) use ($request) {
                $filter = $request->get('filter');
                $query->where(function ($q) use ($filter) {
                    $q->where('description', 'like', "%{$filter}%")
                        ->orWhere('properties', 'like', "%{$filter}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.pages.audit.index', [
            'activities' => $activities,
            'filters' => $request->only('filter'),
        ]);
    }
}
