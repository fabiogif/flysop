<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCitizenAuth;
use App\Http\Resources\ClientResource;
use App\Services\ClientService;

class CitizenAuthController extends Controller
{
    public function __construct(
        protected ClientService $clientService
    ) {
    }

    /**
     * Login leve do cidadão (app/site público): nome + celular obrigatórios, sem senha —
     * ver decisão registrada em docs/specs/flysop.md (MVP sem provedor de SMS/OTP).
     * Idempotente: mesmo celular sempre volta ao mesmo Client (ClientService::registerCitizen).
     */
    public function auth(StoreCitizenAuth $request)
    {
        $client = $this->clientService->registerCitizen($request->validated());

        $token = $client->createToken('citizen')->plainTextToken;

        return response()->json([
            'token' => $token,
            'client' => new ClientResource($client),
        ]);
    }
}
