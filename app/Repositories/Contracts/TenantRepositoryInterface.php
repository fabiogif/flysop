<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

interface TenantRepositoryInterface
{
    public function getAllTenants(int $pre_page): LengthAwarePaginator;

    public function getTenantByUuid(string $uuid): ?Model;

    public function find(int $id): ?Model;

    public function create(array $data): Model;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    public function search(string $filter = null): LengthAwarePaginator;
}
