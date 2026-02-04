<?php

namespace App\Services;

use App\Models\Tenant;
use App\Repositories\Contracts\TenantRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class TenantService
{
    public function __construct(
        protected TenantRepositoryInterface $repository
    ) {
    }

    public function getAllTenants(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getAllTenants($perPage);
    }

    public function getTenantByUuid(string $uuid): ?Model
    {
        return $this->repository->getTenantByUuid($uuid);
    }

    /**
     * Lista paginada para o admin.
     */
    public function getPaginatedList(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getAllTenants($perPage);
    }

    /**
     * Busca por nome ou CNPJ.
     */
    public function search(?string $filter = null): LengthAwarePaginator
    {
        return $this->repository->search($filter);
    }

    public function findOrFail(int $id): Tenant
    {
        $tenant = $this->repository->find($id);
        if (! $tenant) {
            abort(404);
        }

        return $tenant;
    }

    /**
     * Cria tenant e usuário (cadastro público). Planos foram removidos.
     */
    public function make(array $data): Model
    {
        $tenant = $this->createTenantForRegistration($data);

        return $this->createUser($tenant, $data);
    }

    protected function createTenantForRegistration(array $data): Tenant
    {
        return Tenant::create([
            'name' => $data['empresa'] ?? $data['name'],
            'email' => $data['email'],
            'cnpj' => $data['cnpj'],
            'cpf' => $data['cpf'] ?? '',
            'url' => Str::slug($data['empresa'] ?? $data['name']),
            'uuid' => (string) Str::uuid(),
            'subscription' => now(),
            'expires_at' => now()->addDays(7),
        ]);
    }

    protected function createUser(Tenant $tenant, array $data): Model
    {
        return $tenant->users()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }

    /**
     * Cria tenant pelo painel admin (com logo).
     */
    public function createByAdmin(array $data, ?UploadedFile $logo = null): Tenant
    {
        $tenant = auth()->user()->tenant;
        if ($logo && $logo->isValid()) {
            $data['logo'] = $logo->store("tenants/{$tenant->uuid}tenants");
        }
        return $this->repository->create($data);
    }

    /**
     * Atualiza tenant pelo painel admin.
     */
    public function updateByAdmin(int $id, array $data, ?UploadedFile $logo = null): void
    {
        $entity = $this->findOrFail($id);
        if ($logo && $logo->isValid()) {
            if ($entity->logo && Storage::exists($entity->logo)) {
                Storage::delete($entity->logo);
            }
            $data['logo'] = $logo->store("tenants/{$entity->uuid}tenants");
        }
        $this->repository->update($id, $data);
    }

    /**
     * Remove tenant e logo do disco.
     */
    public function deleteByAdmin(int $id): void
    {
        $entity = $this->findOrFail($id);
        if ($entity->logo && Storage::exists($entity->logo)) {
            Storage::delete($entity->logo);
        }
        $this->repository->delete($id);
    }
}
