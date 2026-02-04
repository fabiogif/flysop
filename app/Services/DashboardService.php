<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Occurrences;
use App\Models\Permission;
use App\Models\Role;
use App\Models\TypeOccurrence;
use App\Models\User;
use App\Services\Contracts\DashboardServiceInterface;
use Illuminate\Support\Facades\Auth;

class DashboardService implements DashboardServiceInterface
{
    public function getStats(): array
    {
        $tenant = Auth::user()->tenant;

        return [
            'totalUsers' => User::where('tenant_id', $tenant->id)->count(),
            'totalOcurrencies' => Occurrences::count(),
            'totalCategory' => Category::count(),
            'totalTypeOccurrence' => TypeOccurrence::count(),
            'totalIssuings' => Role::count(),
            'totalRoles' => Role::count(),
            'totalPermission' => Permission::count(),
        ];
    }
}
