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
use Illuminate\Support\Facades\DB;

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
            ->groupBy('status_occurrences.name', 'status_occurrences.sort_order')
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

    /**
     * Indicadores de gestão (Eixo 4). Concluída = status do grupo "resolvidas" do app
     * (OccurrenceService::STATUS_GROUPS), para o painel e o app contarem igual. Tempo médio:
     * da abertura até a PRIMEIRA entrada num status concluído no histórico (reabertura não
     * reinicia a contagem). ponytail: agregações ao vivo, sem cache — ok no volume atual.
     */
    public function getIndicators(): array
    {
        $concluded = OccurrenceService::STATUS_GROUPS['resolvidas'];

        $avgSeconds = DB::table('occurrences as o')
            ->joinSub(
                DB::table('occurrence_status_history as h')
                    ->join('status_occurrences as s', 's.id', '=', 'h.to_status_id')
                    ->whereIn('s.name', $concluded)
                    ->groupBy('h.occurrence_id')
                    ->selectRaw('h.occurrence_id, MIN(h.created_at) as done_at'),
                'done',
                'done.occurrence_id',
                '=',
                'o.id'
            )
            ->value(DB::raw('AVG(EXTRACT(EPOCH FROM (done.done_at - o.created_at)))'));

        $isOpen = fn ($q) => $q->where('is_terminal', false);

        return [
            'concluidas' => Occurrences::whereHas('statusOccurence', fn ($q) => $q->whereIn('name', $concluded))->count(),
            'atrasadas' => Occurrences::whereNotNull('due_at')->where('due_at', '<', now())
                ->whereHas('statusOccurence', $isOpen)->count(),
            'tempoMedioHoras' => $avgSeconds !== null ? round($avgSeconds / 3600, 1) : null,
            'porBairro' => Occurrences::whereNotNull('neighborhood')->where('neighborhood', '!=', '')
                ->selectRaw('neighborhood as name, COUNT(*) as total')
                ->selectRaw('SUM(CASE WHEN status_occurrences_id IN (SELECT id FROM status_occurrences WHERE is_terminal = false) THEN 1 ELSE 0 END) as abertas')
                ->groupBy('neighborhood')
                ->orderByDesc('abertas')->orderByDesc('total')
                ->limit(10)->get(),
            'porSecretaria' => Occurrences::join('issuings', 'issuings.id', '=', 'occurrences.issuings_id')
                ->selectRaw('issuings.name as name, COUNT(*) as total')
                ->groupBy('issuings.name')->orderByDesc('total')->get(),
            'categorias' => Occurrences::join('type_occurrences', 'type_occurrences.id', '=', 'occurrences.type_occurrences_id')
                ->selectRaw('type_occurrences.name as name, COUNT(*) as total')
                ->groupBy('type_occurrences.name')->orderByDesc('total')
                ->limit(10)->get(),
        ];
    }
}
