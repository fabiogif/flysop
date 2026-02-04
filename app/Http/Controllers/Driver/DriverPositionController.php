<?php

namespace App\Http\Controllers\Driver;

use App\Events\DriverPositionUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDriverPositionRequest;
use App\Models\DriverPosition;
use App\Models\Occurrences;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverPositionController extends Controller
{
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

        $driverId = $driver ? $driver->id : null;

        if (! $driverId) {
            return response()->json(['message' => 'Motorista não encontrado.'], 403);
        }

        $occurrenceId = $request->input('occurrence_id');
        if ($occurrenceId !== null) {
            $occurrence = Occurrences::find($occurrenceId);
            if (! $occurrence || (int) $occurrence->driver_id !== $driverId) {
                return response()->json(['message' => 'Ocorrência não atribuída a você.'], 403);
            }
        }

        $position = DriverPosition::create([
            'driver_id' => $driverId,
            'occurrence_id' => $occurrenceId,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'accuracy' => $request->input('accuracy'),
        ]);

        if ($position->occurrence_id !== null) {
            $position->load('driver');
            broadcast(new DriverPositionUpdated($position))->toOthers();
        }

        return response()->json([
            'message' => 'Posição registrada.',
            'id' => $position->id,
            'created_at' => $position->created_at?->toIso8601String(),
        ], 201);
    }
}
