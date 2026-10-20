<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateAuth;
use App\Http\Resources\ClientResource;
use App\Http\Resources\ManagerResource;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthClientController extends Controller
{
    public function auth(StoreUpdateAuth $request)
    {

        $client = Client::where('email', $request->email)->first();

        if (!$client || !Hash::check($request->password, $client->password)) {
            return response()->json(['menssage' => 'Credenciais inválidas'], 404);
        }

        $token = $client->createToken($request->device_name)->plainTextToken;

        return response()->json(['token', $token]);
    }


    public function me(Request $request)
    {
        $user = $request->user();

        // O mesmo /auth/me atende cidadão (Client) e gestor (User) — o app decide a navegação por "role".
        return $user instanceof User ? new ManagerResource($user) : new ClientResource($user);
    }

    public function logout(Request $request)
    {
        // Só o token deste aparelho: o gestor pode estar logado no app e no painel ao mesmo tempo.
        $request->user()->currentAccessToken()->delete();

        return response()->json([], 204);
    }
}
