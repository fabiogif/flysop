<?php

namespace App\Repositories\Contracts;

interface ClientRepositoryInterface
{
    public function createNewClient(array $data);
    public function getClienteById(int $id);
    public function firstOrCreateByPhone(int $tenantId, string $phone, array $data);
}
