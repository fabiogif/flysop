<?php

namespace App\Repositories;

use App\Models\Client;
use App\Repositories\Contracts\ClientConsumerRepositoryInterface;

class ClientConsumerRepository implements ClientConsumerRepositoryInterface
{
    protected $entity;

    public function __construct(Client $client)
    {
        $this->entity = $client;
    }


    public function createNewClientConsumer(array $data)
    {
        $data['password'] = bcrypt($data['password']);

        return $this->entity->create($data);
    }

    public function getClienteConsumerById(int $id)
    {
    //return $this->entity->find($id);
    }
}
