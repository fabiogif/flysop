<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateAuth;
use App\Http\Resources\ManagerResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StaffAuthController extends Controller
{
    /**
     * Login do gestor de órgão no app (mesmas credenciais do painel web). Público por
     * necessidade (é o login), com throttle na rota; só emite token para quem é gestor.
     */
    public function login(StoreUpdateAuth $request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'E-mail ou senha inválidos.'], 401);
        }

        if (! $user->isManager()) {
            return response()->json(['message' => 'Seu usuário não está vinculado a um órgão.'], 403);
        }

        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new ManagerResource($user),
        ]);
    }
}
