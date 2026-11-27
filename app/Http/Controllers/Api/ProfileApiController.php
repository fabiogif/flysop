<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Resources\ClientResource;
use App\Http\Resources\PublicProfileResource;
use App\Models\Client;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileApiController extends Controller
{
    /**
     * Exibe o perfil público de um cidadão pelo @username.
     */
    public function show(string $username)
    {
        $tenantId = Tenant::query()->value('id') ?? 1;

        $client = Client::where('tenant_id', $tenantId)
            ->where('username', $username)
            ->first();

        if (!$client) {
            return response()->json(['message' => 'Perfil não encontrado.'], 404);
        }

        return new PublicProfileResource($client);
    }

    /**
     * Atualiza nome e e-mail do cidadão autenticado.
     */
    public function update(UpdateProfileRequest $request)
    {
        $client = $request->user();

        $client->update($request->validated());

        return new ClientResource($client);
    }

    /**
     * Upload de avatar do cidadão autenticado.
     */
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
        ], [
            'avatar.required' => 'Envie uma imagem para o avatar.',
            'avatar.image' => 'O arquivo deve ser uma imagem válida.',
            'avatar.mimes' => 'A imagem deve estar no formato JPEG, PNG ou WEBP.',
            'avatar.max' => 'A imagem de avatar deve ter no máximo 2 MB.',
        ]);

        $client = $request->user();

        $path = $request->file('avatar')->store("avatars/{$client->uuid}");

        $avatarUrl = Storage::url($path);

        $client->update([
            'avatar_url' => $avatarUrl,
        ]);

        return response()->json([
            'avatar_url' => $avatarUrl,
            'client' => new PublicProfileResource($client),
        ]);
    }
}
