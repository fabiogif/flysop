<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsManager
{
    /**
     * Rotas /api/manager/*: só usuário do painel vinculado a um órgão. Token de cidadão
     * (Client) também é Sanctum, então a checagem de tipo é obrigatória aqui.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->isManager()) {
            return response()->json(['message' => 'Acesso restrito a gestores de órgão.'], 403);
        }

        return $next($request);
    }
}
