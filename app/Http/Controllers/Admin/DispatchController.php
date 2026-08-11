<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Occurrences;
use App\Models\Priority;
use App\Models\StatusOccurrence;
use App\Models\TypeOccurrence;
use App\Services\DispatchService;
use App\Services\OccurrenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DispatchController extends Controller
{
    public function __construct(
        protected DispatchService $dispatchService,
        protected OccurrenceService $occurrenceService
    ) {
    }

    /**
     * Console de despacho (Fase "Operacional" do plano de evolução do mapa): lista
     * filtrável e mapa lado a lado, sobre os mesmos endpoints já usados pelo dashboard
     * (occurrencesRecent/occurrencesHeatmap/driversLastPositions) — não duplica consulta,
     * só oferece um layout operacional diferente para os mesmos dados.
     */
    public function console(): View
    {
        $this->authorize('viewAny', Occurrences::class);

        $filterOptions = [
            'statusOccurrences' => StatusOccurrence::orderBy('sort_order')->get(),
            'typeOccurrences' => TypeOccurrence::orderBy('name')->get(),
            'priorities' => Priority::orderBy('weight', 'desc')->get(),
            'drivers' => Driver::where('tenant_id', auth()->user()->tenant_id)->orderBy('name')->get(),
        ];

        return view('admin.pages.dispatch.console', ['filterOptions' => $filterOptions]);
    }

    /**
     * Ranking de motoristas disponíveis mais próximos da ocorrência (sugestão, não atribuição).
     */
    public function suggest(int $occurrenceId): JsonResponse
    {
        $occurrence = $this->occurrenceService->findOrFail($occurrenceId);
        $this->authorize('update', $occurrence);

        try {
            $drivers = $this->dispatchService->suggestDrivers($occurrence);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['drivers' => $drivers]);
    }

    /**
     * Confirma a atribuição de um motorista sugerido — ação enxuta do console de despacho,
     * distinta do formulário completo de edição (ver OccurrenceService::assignDriver()).
     */
    public function assign(Request $request, int $occurrenceId): JsonResponse
    {
        $occurrence = $this->occurrenceService->findOrFail($occurrenceId);
        $this->authorize('update', $occurrence);

        $validated = $request->validate([
            'driver_id' => 'required|integer|exists:drivers,id',
        ]);

        $updated = $this->occurrenceService->assignDriver($occurrenceId, (int) $validated['driver_id']);

        return response()->json([
            'message' => 'Motorista atribuído.',
            'occurrence' => [
                'id' => $updated->id,
                'driver_id' => $updated->driver_id,
                'status' => $updated->statusOccurence?->name,
            ],
        ]);
    }
}
