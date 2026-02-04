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
}
