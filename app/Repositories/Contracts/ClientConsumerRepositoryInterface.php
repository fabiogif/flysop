<?php

namespace App\Repositories\Contracts;

interface ClientConsumerRepositoryInterface
{
    public function createNewClientConsumer(array $data);

    public function getClienteConsumerById(int $id);
}
