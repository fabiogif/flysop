<?php

namespace App\Repositories;

use App\Models\Driver;
use App\Repositories\Contracts\DriverRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DriverRepository implements DriverRepositoryInterface
{
    public function __construct(
        protected Driver $entity
    ) {
    }

    /**
     * Haversine em SQL puro (sem PostGIS/earthdistance — ver docs/specs/modules.md sobre a
     * decisão da Fase 5). LATERAL join pega só a posição mais recente de cada motorista,
     * evitando trazer todo o histórico de driver_positions para calcular isso em PHP.
     */
    public function nearestAvailable(float $latitude, float $longitude, ?int $typeOccurrenceId = null, int $limit = 5): Collection
    {
        $rows = DB::select(
            <<<'SQL'
                SELECT
                    d.id,
                    d.name,
                    d.status,
                    d.team_id,
                    t.name AS team_name,
                    dp.latitude AS last_latitude,
                    dp.longitude AS last_longitude,
                    dp.created_at AS last_position_at,
                    (6371 * acos(LEAST(1.0, GREATEST(-1.0,
                        cos(radians(?)) * cos(radians(dp.latitude)) * cos(radians(dp.longitude) - radians(?))
                        + sin(radians(?)) * sin(radians(dp.latitude))
                    )))) AS distance_km
                FROM drivers d
                JOIN LATERAL (
                    SELECT latitude, longitude, created_at
                    FROM driver_positions
                    WHERE driver_id = d.id
                    ORDER BY created_at DESC
                    LIMIT 1
                ) dp ON true
                LEFT JOIN teams t ON t.id = d.team_id
                WHERE d.status = ?
                    AND (?::bigint IS NULL OR t.type_occurrences_id IS NULL OR t.type_occurrences_id = ?::bigint)
                ORDER BY distance_km ASC
                LIMIT ?
            SQL,
            [
                $latitude,
                $longitude,
                $latitude,
                Driver::STATUS_DISPONIVEL,
                $typeOccurrenceId,
                $typeOccurrenceId,
                $limit,
            ]
        );

        return collect($rows)->map(fn ($row) => [
            'id' => (int) $row->id,
            'name' => $row->name,
            'status' => $row->status,
            'team_id' => $row->team_id ? (int) $row->team_id : null,
            'team_name' => $row->team_name,
            'distance_km' => round((float) $row->distance_km, 2),
            'last_position_at' => $row->last_position_at,
            // Necessário para desenhar o candidato no mapa do console de despacho — a query
            // já buscava essas colunas, só não estavam sendo expostas na resposta.
            'latitude' => (float) $row->last_latitude,
            'longitude' => (float) $row->last_longitude,
        ]);
    }
}
