<?php

namespace App\Services;

use App\Repositories\ClientRepository;
use App\Repositories\Contracts\ClientRepositoryInterface;


class ClientService
{
    protected $clientRepository;


    public function __construct(ClientRepository $clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }


    public function createNewClient(array $data)
    {
        return $this->clientRepository->createNewClient($data);
    }

    public function getClienteById($id)
    {
        return $this->clientRepository->getClienteById($id);

    }
}
