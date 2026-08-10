<?php

namespace App\Services;

use App\Events\DriverPositionUpdated;
use App\Models\DriverPosition;
use App\Models\Occurrences;
use InvalidArgumentException;

class DriverPositionService
{
    /**
     * Registra a posição do motorista e, se vinculada a uma ocorrência, dispara o evento em tempo real.
     *
     * Ponto único de gravação de posição do motorista (antes duplicado entre
     * Api\Driver\DriverLocationController e Driver\DriverPositionController).
     *
     * @throws InvalidArgumentException Quando a ocorrência informada não está atribuída a este motorista.
     */
    public function record(
        int $driverId,
        float $latitude,
        float $longitude,
        ?int $occurrenceId = null,
        ?float $accuracy = null
    ): DriverPosition {
        if ($occurrenceId !== null) {
            $occurrence = Occurrences::find($occurrenceId);
            if (! $occurrence || (int) $occurrence->driver_id !== $driverId) {
                throw new InvalidArgumentException('Ocorrência não atribuída a este motorista.');
            }
        }

        $position = DriverPosition::create([
            'driver_id' => $driverId,
            'occurrence_id' => $occurrenceId,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'accuracy' => $accuracy,
        ]);

        if ($position->occurrence_id !== null) {
            $position->load('driver');
            broadcast(new DriverPositionUpdated($position))->toOthers();
        }

        return $position;
    }
}
