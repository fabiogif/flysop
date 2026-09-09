<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Tenant;
use App\Repositories\ClientRepository;
use App\Repositories\Contracts\ClientRepositoryInterface;
use Illuminate\Support\Str;

class ClientService
{
    /**
     * Placeholder de "clients_id" para ocorrência enviada como anônima — um único
     * registro por tenant (não um novo Client a cada submissão), reconhecido pelo admin
     * via este telefone reservado (não é um número real, nunca colide com cadastro).
     */
    public const ANONYMOUS_PHONE = 'anonimo';

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

    /**
     * Login leve do cidadão (app/site público): nome + celular obrigatórios, sem senha.
     * Idempotente por celular — quem já se cadastrou volta a "logar" automaticamente.
     */
    public function registerCitizen(array $data): Client
    {
        $tenantId = Tenant::query()->value('id') ?? 1;

        return $this->clientRepository->firstOrCreateByPhone(
            $tenantId,
            $data['phone'],
            [
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'uuid' => (string) Str::uuid(),
            ]
        );
    }

    /**
     * Client placeholder usado quando a ocorrência é enviada como anônima (ver
     * OccurrenceApiController::createNewOccurrence) — occurrences.clients_id continua
     * NOT NULL, então aponta sempre para este registro em vez de mudar o schema.
     */
    public function anonymousClient(): Client
    {
        $tenantId = Tenant::query()->value('id') ?? 1;

        return $this->clientRepository->firstOrCreateByPhone(
            $tenantId,
            self::ANONYMOUS_PHONE,
            [
                'name' => 'Anônimo',
                'uuid' => (string) Str::uuid(),
            ]
        );
    }
}
