<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDriverPositionRequest;
use App\Services\DriverPositionService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class DriverPositionController extends Controller
{
    public function __construct(protected DriverPositionService $driverPositionService)
    {
    }

    /**
     * Motorista envia sua posição (lat/lng). Opcionalmente vincula à ocorrência em deslocamento.
     * Rate limit: 1 req a cada 3 s por usuário (evitar spam).
     */
    public function store(StoreDriverPositionRequest $request): JsonResponse
    {
        $user = $request->user();
        $driver = $user->driver;

        if (! $user->isAdmin() && ! $driver) {
            return response()->json(['message' => 'Usuário não vinculado a um motorista.'], 403);
        }

        if (! $driver) {
            return response()->json(['message' => 'Motorista não encontrado.'], 403);
        }

        try {
            $position = $this->driverPositionService->record(
                driverId: $driver->id,
                latitude: (float) $request->latitude,
                longitude: (float) $request->longitude,
                occurrenceId: $request->input('occurrence_id') !== null ? (int) $request->input('occurrence_id') : null,
                accuracy: $request->input('accuracy') !== null ? (float) $request->input('accuracy') : null,
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        return response()->json([
            'message' => 'Posição registrada.',
            'id' => $position->id,
            'created_at' => $position->created_at?->toIso8601String(),
        ], 201);
    }
}
