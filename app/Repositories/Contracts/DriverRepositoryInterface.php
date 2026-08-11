<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface DriverRepositoryInterface
{
    /**
     * Ranking de motoristas disponíveis mais próximos de um ponto, usando a última
     * posição GPS conhecida de cada um. Quando $typeOccurrenceId é informado, equipes
     * com especialidade definida (teams.type_occurrences_id) só entram se compatível;
     * motoristas sem equipe ou com equipe sem especialidade definida sempre entram.
     */
    public function nearestAvailable(float $latitude, float $longitude, ?int $typeOccurrenceId = null, int $limit = 5): Collection;
}
