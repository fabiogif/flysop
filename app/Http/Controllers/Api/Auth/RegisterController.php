<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreUpdateClient;
use App\Http\Resources\ClientResource;
use App\Services\ClientService;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    protected $clientService;


    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    public function store(StoreUpdateClient $request)
    {
        $client = $this->clientService->createNewClient($request->all());

        if (!$client) {
            return response()->json(['message', 'Client not found'], 404);
        }

        return new ClientResource($client);
    }

    public function show($id)
    {
        $client = $this->clientService->getClienteById($id);

        if (!$client) {
            return response()->json(['message' => 'Cliente não encontrado'], 404);
        }
        return new ClientResource($client);
    }
}