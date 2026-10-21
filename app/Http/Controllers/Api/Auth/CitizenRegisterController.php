<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCitizenRegister;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CitizenRegisterController extends Controller
{
    /**
     * Cadastro completo do cidadão (e-mail + senha + termos).
     */
    public function register(StoreCitizenRegister $request)
    {
        $tenantId = Tenant::query()->value('id') ?? 1;

        // Se já existe com este e-mail no tenant
        $existing = Client::where('tenant_id', $tenantId)
            ->where('email', $request->email)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Já existe uma conta cadastrada com este e-mail. Por favor, faça login.',
                'errors' => ['email' => ['Este e-mail já está em uso.']],
            ], 422);
        }

        $client = Client::create([
            'tenant_id' => $tenantId,
            'uuid' => (string) Str::uuid(),
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'marketing_opt_in' => (bool) $request->marketing_opt_in,
            'terms_accepted_at' => now(),
        ]);

        $token = $client->createToken('citizen')->plainTextToken;

        return response()->json([
            'token' => $token,
            'client' => new ClientResource($client),
        ], 201);
    }
}
