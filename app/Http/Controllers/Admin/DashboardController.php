<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\DriverPosition;
use App\Models\Occurrences;
use App\Models\Priority;
use App\Models\StatusOccurrence;
use App\Models\TypeOccurrence;
use App\Services\Contracts\DashboardServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardServiceInterface $dashboardService
    ) {
    }

    public function home()
    {
        $stats = $this->dashboardService->getStats();
        $charts = $this->dashboardService->getChartsData();

        $filterOptions = [
            'statusOccurrences' => StatusOccurrence::orderBy('sort_order')->get(),
            'typeOccurrences' => TypeOccurrence::orderBy('name')->get(),
            'priorities' => Priority::orderBy('weight', 'desc')->get(),
            'drivers' => Driver::where('tenant_id', auth()->user()->tenant_id)->orderBy('name')->get(),
        ];

        return view('admin.pages.home.index', $stats + ['charts' => $charts, 'filterOptions' => $filterOptions]);
    }

    /**
     * Exporta métricas de uso da organização em CSV.
     */
    public function exportUsage(): StreamedResponse
    {
        $stats = $this->dashboardService->getStats();
        $filename = 'uso-organizacao-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($stats) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['metrica', 'valor']);
            foreach ($stats as $key => $value) {
                if (is_scalar($value)) {
                    fputcsv($out, [$key, $value]);
                }
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Retorna ocorrências recentes para atualização em tempo real (polling/push) e
     * alimenta o mapa do dashboard. Aceita filtros opcionais (Fase 4): status_occurrences_id,
     * type_occurrences_id, priority_id, driver_id, date_from, date_to (sobre created_at).
     */
    public function occurrencesRecent(Request $request): JsonResponse
    {
        $limit = (int) $request->get('limit', 10);
        $occurrences = Occurrences::with(['statusOccurence', 'typeOccurrence', 'priority'])
            ->when($request->filled('status_occurrences_id'), fn ($q) => $q->where('status_occurrences_id', $request->status_occurrences_id))
            ->when($request->filled('type_occurrences_id'), fn ($q) => $q->where('type_occurrences_id', $request->type_occurrences_id))
            ->when($request->filled('priority_id'), fn ($q) => $q->where('priority_id', $request->priority_id))
            ->when($request->filled('driver_id'), fn ($q) => $q->where('driver_id', $request->driver_id))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn ($o) => [
                'id' => $o->id,
                'title' => $o->title,
                'name' => $o->name,
                'address' => $o->address,
                'latitude' => $o->latitude ? (float) $o->latitude : null,
                'longitude' => $o->longitude ? (float) $o->longitude : null,
                'status' => $o->statusOccurence?->name ?? '—',
                'type' => $o->typeOccurrence?->name ?? '—',
                'priority' => $o->priority?->name,
                'priority_color' => $o->priority?->color,
                'updated_at' => $o->updated_at?->toIso8601String(),
                'updated_at_human' => $o->updated_at?->diffForHumans(),
            ]);

        return response()->json(['occurrences' => $occurrences]);
    }

    /**
     * Pontos para o mapa de calor (Fase 4): todas as ocorrências com lat/lng, sem limite de
     * "recentes" — usado separadamente do card de lista para não sobrecarregar o polling.
     */
    public function occurrencesHeatmap(Request $request): JsonResponse
    {
        $points = Occurrences::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->get(['latitude', 'longitude'])
            ->map(fn ($o) => ['lat' => (float) $o->latitude, 'lng' => (float) $o->longitude]);

        return response()->json(['points' => $points]);
    }

    /**
     * Retorna a última posição de cada motorista em deslocamento (posição recente com occurrence_id).
     * Usado no mapa do dashboard para exibir marcadores dos motoristas.
     */
    public function driversLastPositions(Request $request): JsonResponse
    {
        $minutes = (int) $request->get('minutes', 30);
        $positions = DriverPosition::with('driver')
            ->whereNotNull('occurrence_id')
            ->recent($minutes)
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('driver_id')
            ->values()
            ->map(fn ($p) => [
                'driver_id' => $p->driver_id,
                'driver_name' => $p->driver?->name ?? 'Motorista',
                'occurrence_id' => $p->occurrence_id,
                'latitude' => (float) $p->latitude,
                'longitude' => (float) $p->longitude,
                'created_at' => $p->created_at?->toIso8601String(),
            ]);

        return response()->json(['drivers' => $positions]);
    }
}
