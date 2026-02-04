<?php

namespace App\Services;

use App\Repositories\Contracts\ClientConsumerRepositoryInterface;


class ClientConsumerService
{
    protected $clientConsumerRepository;

    public function __construct(ClientConsumerRepositoryInterface $clientConsumerRepository)
    {
        $this->clientConsumerRepository = $clientConsumerRepository;
    }


    public function createNewClientConsumer(array $data)
    {
        return $this->clientConsumerRepository->createNewClientConsumer($data);
    }
}
