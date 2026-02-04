<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsDriver
{
    /**
     * Garante que o usuário autenticado está vinculado a um motorista (Driver).
     * Usado nas rotas /driver/* para acesso exclusivo do painel do motorista.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        $user = $request->user();

        if ($user->isAdmin()) {
            return $next($request);
        }

        try {
            $driver = $user->driver;
        } catch (\Throwable $e) {
            \Log::error('EnsureUserIsDriver: ' . $e->getMessage(), ['user_id' => $user->id]);
            abort(500, 'Erro ao verificar vínculo com motorista. Veja storage/logs/laravel.log.');
        }

        if (! $driver) {
            abort(403, 'Acesso restrito ao painel do motorista. Vincule seu usuário a um motorista.');
        }

        return $next($request);
    }
}
