<?php

namespace App\Services;

use App\Models\Occurrences;
use App\Repositories\Contracts\DriverRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * Despacho inteligente (Fase 5): sugere os motoristas disponíveis mais próximos de uma
 * ocorrência. Só sugere — quem atribui é o Atendente/Supervisor via
 * OccurrenceService::updateForAdmin (humano no loop, sem atribuição automática).
 */
class DispatchService
{
    public function __construct(
        protected DriverRepositoryInterface $driverRepository
    ) {
    }

    /**
     * @throws \InvalidArgumentException Quando a ocorrência não tem latitude/longitude.
     */
    public function suggestDrivers(Occurrences $occurrence, int $limit = 5): Collection
    {
        if (! $occurrence->latitude || ! $occurrence->longitude) {
            throw new \InvalidArgumentException('Ocorrência sem localização (latitude/longitude) para calcular distância.');
        }

        return $this->driverRepository->nearestAvailable(
            (float) $occurrence->latitude,
            (float) $occurrence->longitude,
            $occurrence->type_occurrences_id,
            $limit
        );
    }
}
