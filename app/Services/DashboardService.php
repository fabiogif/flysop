<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Occurrences;
use App\Models\Permission;
use App\Models\Role;
use App\Models\TypeOccurrence;
use App\Models\User;
use App\Services\Contracts\DashboardServiceInterface;
use App\Models\Issuing;
use App\Models\Driver;
use App\Models\Department;
use App\Models\Team;
use App\Models\Priority;
use App\Models\StatusOccurrence;
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
            'totalIssuings' => Issuing::count(),
            'totalRoles' => Role::count(),
            'totalPermission' => Permission::count(),
            'totalDrivers' => Driver::where('tenant_id', $tenant->id)->count(),
            'totalDepartments' => Department::count(),
            'totalTeams' => Team::count(),
            'totalPriorities' => Priority::count(),
            'totalStatusOccurrences' => StatusOccurrence::count(),
            'organisation' => $tenant->name,
            'exported_at' => now()->toIso8601String(),

            // KPIs operacionais (Fase 4)
            'occurrencesAbertas' => Occurrences::whereHas('statusOccurence', fn ($q) => $q->where('is_terminal', false))->count(),
            'occurrencesEmAtendimento' => Occurrences::whereHas('statusOccurence', fn ($q) => $q->where('name', 'Em atendimento'))->count(),
            'occurrencesFinalizadasHoje' => Occurrences::whereHas('statusOccurence', fn ($q) => $q->where('is_terminal', true))
                ->whereDate('updated_at', today())
                ->count(),
            'occurrencesSlaEstourado' => Occurrences::whereNotNull('due_at')
                ->where('due_at', '<', now())
                ->whereHas('statusOccurence', fn ($q) => $q->where('is_terminal', false))
                ->count(),
            'occurrencesSlaEmRisco' => Occurrences::whereNotNull('due_at')
                ->whereBetween('due_at', [now(), now()->addHours(2)])
                ->whereHas('statusOccurence', fn ($q) => $q->where('is_terminal', false))
                ->count(),
        ];
    }

    /**
     * Dados agregados para os gráficos do dashboard (Chart.js): ocorrências dos últimos
     * 14 dias, por status atual e por prioridade. Consultas simples de agregação — sem
     * cache/materialização, aceitável para o volume atual (ver docs/specs/modules.md).
     */
    public function getChartsData(): array
    {
        $since = now()->subDays(13)->startOfDay();

        $byDayRaw = Occurrences::selectRaw("DATE(created_at) as day, COUNT(*) as total")
            ->where('created_at', '>=', $since)
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $byDay = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $byDay[$date] = (int) ($byDayRaw[$date] ?? 0);
        }

        $byStatus = Occurrences::join('status_occurrences', 'status_occurrences.id', '=', 'occurrences.status_occurrences_id')
            ->selectRaw('status_occurrences.name as name, COUNT(*) as total')
            ->groupBy('status_occurrences.name')
            ->orderBy('status_occurrences.sort_order')
            ->pluck('total', 'name');

        $byPriority = Occurrences::join('priorities', 'priorities.id', '=', 'occurrences.priority_id')
            ->selectRaw('priorities.name as name, priorities.color as color, COUNT(*) as total')
            ->groupBy('priorities.name', 'priorities.color')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => ['name' => $row->name, 'color' => $row->color, 'total' => (int) $row->total]);

        return [
            'byDay' => $byDay,
            'byStatus' => $byStatus,
            'byPriority' => $byPriority,
        ];
    }
}
