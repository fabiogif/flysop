<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DispatchService;
use App\Services\OccurrenceService;
use Illuminate\Http\JsonResponse;

class DispatchController extends Controller
{
    public function __construct(
        protected DispatchService $dispatchService,
        protected OccurrenceService $occurrenceService
    ) {
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
}
