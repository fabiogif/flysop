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

    /**
     * Autenticação / Cadastro federado via Social Auth (Apple ou Google).
     */
    public function socialAuth(Request $request)
    {
        $request->validate([
            'provider' => ['required', 'in:google,apple'],
            'provider_id' => ['required', 'string'],
            'email' => ['nullable', 'email'],
            'name' => ['nullable', 'string', 'max:60'],
        ]);

        $tenantId = Tenant::query()->value('id') ?? 1;
        $providerColumn = $request->provider === 'google' ? 'google_id' : 'apple_id';

        // 1. Localiza pelo provider_id
        $client = Client::where('tenant_id', $tenantId)
            ->where($providerColumn, $request->provider_id)
            ->first();

        // 2. Se não achou pelo provider_id mas tem e-mail, vincula
        if (!$client && $request->filled('email')) {
            $client = Client::where('tenant_id', $tenantId)
                ->where('email', $request->email)
                ->first();

            if ($client) {
                $client->update([$providerColumn => $request->provider_id]);
            }
        }

        // 3. Se ainda não existe, cria conta
        if (!$client) {
            $client = Client::create([
                'tenant_id' => $tenantId,
                'uuid' => (string) Str::uuid(),
                'name' => $request->name ?: ($request->provider === 'google' ? 'Usuário Google' : 'Usuário Apple'),
                'email' => $request->email,
                $providerColumn => $request->provider_id,
                'terms_accepted_at' => now(),
            ]);
        }

        $token = $client->createToken('citizen')->plainTextToken;

        return response()->json([
            'token' => $token,
            'client' => new ClientResource($client),
        ]);
    }
}
