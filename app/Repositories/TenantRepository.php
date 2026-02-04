<?php

namespace App\Repositories;

use App\Models\Tenant;
use App\Repositories\Contracts\TenantRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class TenantRepository implements TenantRepositoryInterface
{
    public function __construct(
        protected Tenant $entity
    ) {
    }

    public function getAllTenants(int $pre_page): LengthAwarePaginator
    {
        return $this->entity->latest()->paginate($pre_page);
    }

    public function getTenantByUuid(string $uuid): ?Model
    {
        return $this->entity->where('uuid', $uuid)->first();
    }

    public function find(int $id): ?Model
    {
        return $this->entity->find($id);
    }

    public function create(array $data): Model
    {
        return $this->entity->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $tenant = $this->entity->find($id);

        return $tenant ? $tenant->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $tenant = $this->entity->find($id);

        return $tenant ? (bool) $tenant->delete() : false;
    }

    public function search(?string $filter = null): LengthAwarePaginator
    {
        return $this->entity->when($filter, function ($query) use ($filter) {
            $query->where(function ($q) use ($filter) {
                $q->orWhere('cnpj', $filter)
                    ->orWhere('name', 'LIKE', "%{$filter}%");
            });
        })->latest()->paginate();
    }
}
