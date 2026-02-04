<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DriverPosition;
use App\Models\Occurrences;
use App\Services\Contracts\DashboardServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardServiceInterface $dashboardService
    ) {
    }

    public function home()
    {
        $stats = $this->dashboardService->getStats();

        return view('admin.pages.home.index', $stats);
    }

    /**
     * Retorna ocorrências recentes para atualização em tempo real (polling).
     */
    public function occurrencesRecent(Request $request): JsonResponse
    {
        $limit = (int) $request->get('limit', 10);
        $occurrences = Occurrences::with(['statusOccurence', 'typeOccurrence'])
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
                'updated_at' => $o->updated_at?->toIso8601String(),
                'updated_at_human' => $o->updated_at?->diffForHumans(),
            ]);

        return response()->json(['occurrences' => $occurrences]);
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
