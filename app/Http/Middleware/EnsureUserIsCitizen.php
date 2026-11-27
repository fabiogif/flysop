<?php

namespace App\Http\Middleware;

use App\Models\Client;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsCitizen
{
    /**
     * Rotas do cidadão (perfil, minhas ocorrências): bloqueia token de gestor (User), que
     * também é Sanctum e quebraria as regras que assumem um Client.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() instanceof Client) {
            return response()->json(['message' => 'Acesso restrito a cidadãos.'], 403);
        }

        return $next($request);
    }
}
