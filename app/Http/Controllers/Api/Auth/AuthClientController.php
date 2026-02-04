<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateAuth;
use App\Http\Resources\ClientResource;
use App\Models\Client;
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
        $client = $request->user();
        return new ClientResource($client);
    }

    public function logout(Request $request)
    {
        $client = $request->user();

        $client->tokens()->delete();

        return response()->json([], 204);
    }
}
