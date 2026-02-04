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

    public function getAllOccurrences(int $pre_page): LengthAwarePaginator
    {
        return $this->entity->Occurrence();
    }

    public function getOccurrenceById(string $id): ?Model
    {
        return $this->entity->where('id', $id)->first();
    }

    public function getOccurrenceByClientId(int $clientId): LengthAwarePaginator
    {
        return $this->entity->OccurrenceByClientId($clientId);
    }

    public function createNewOccurrence(array $data): Model
    {
        return $this->entity->create($data);
    }

    public function getPaginatedList(int $perPage = 15): LengthAwarePaginator
    {
        return $this->entity->Occurrence(null);
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

    public function search(?string $filter = null): LengthAwarePaginator
    {
        return $this->entity->Occurrence($filter);
    }
}
