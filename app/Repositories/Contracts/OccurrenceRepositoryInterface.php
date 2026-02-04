<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

interface OccurrenceRepositoryInterface
{
    public function getAllOccurrences(int $pre_page);

    public function getOccurrenceById(string $id): ?Model;

    public function getOccurrenceByClientId(int $clientId): LengthAwarePaginator;

    public function createNewOccurrence(array $data): Model;

    public function getPaginatedList(int $perPage = 15): LengthAwarePaginator;

    public function find(int $id): ?Model;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    public function search(?string $filter = null): LengthAwarePaginator;
}
