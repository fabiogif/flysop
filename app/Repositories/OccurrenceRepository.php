<?php

namespace App\Repositories;

use App\Models\Occurrences;
use App\Repositories\Contracts\OccurrenceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class OccurrenceRepository implements OccurrenceRepositoryInterface
{
    public function __construct(
        protected Occurrences $entity
    ) {
    }

    public function getOccurrenceById(string $id): ?Model
    {
        return $this->entity->where('id', $id)->first();
    }

    public function countByStatusName(?int $issuingId = null): array
    {
        return $this->entity->newQuery()
            ->join('status_occurrences', 'status_occurrences.id', '=', 'occurrences.status_occurrences_id')
            ->when($issuingId, fn ($q) => $q->where('occurrences.issuings_id', $issuingId))
            ->groupBy('status_occurrences.name')
            ->selectRaw('status_occurrences.name, count(*) as total')
            ->pluck('total', 'name')
            ->map(fn ($total) => (int) $total)
            ->all();
    }

    public function getOccurrenceByClientId(int $clientId): LengthAwarePaginator
    {
        return $this->entity->OccurrenceByClientId($clientId);
    }

    public function createNewOccurrence(array $data): Model
    {
        return $this->entity->create($data);
    }

    public function getPaginatedList(int $perPage = 15, ?int $issuingId = null): LengthAwarePaginator
    {
        return $this->entity->Occurrence(null, $issuingId);
    }

    public function find(int $id): ?Model
    {
        return $this->entity->find($id);
    }

    public function update(int $id, array $data): bool
    {
        $occurrence = $this->entity->find($id);

        return $occurrence ? $occurrence->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $occurrence = $this->entity->find($id);

        return $occurrence ? (bool) $occurrence->delete() : false;
    }

    public function search(?string $filter = null, ?int $issuingId = null): LengthAwarePaginator
    {
        return $this->entity->Occurrence($filter, $issuingId);
    }
}
