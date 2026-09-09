<?php

namespace App\Repositories;

use App\Models\Client;
use App\Repositories\Contracts\ClientRepositoryInterface;
use Illuminate\Support\Str;

class ClientRepository implements ClientRepositoryInterface
{
    protected $entity;

    public function __construct(Client $client)
    {
        $this->entity = $client;
    }


    public function createNewClient(array $data)
    {
        $data['password'] = bcrypt($data['password']);

        return $this->entity->create($data);
    }

    public function getClienteById(int $id)
    {
        return $this->entity->find($id);
    }

    /**
     * Idempotente: mesmo celular (por tenant) sempre retorna o mesmo Client, sem duplicar
     * cadastro a cada novo login leve do cidadão (app/site público).
     */
    public function firstOrCreateByPhone(int $tenantId, string $phone, array $data)
    {
        return $this->entity->firstOrCreate(
            ['tenant_id' => $tenantId, 'phone' => $phone],
            $data
        );
    }
}
