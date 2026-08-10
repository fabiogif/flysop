<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Garante que /admin só seja acessível a quem tem permissão administrativa
 * (qualquer gate do menu, exceto apenas driver.panel).
 */
class EnsureAdminPanelAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            abort(403);
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        $permissions = $user->permissions();
        $adminPermissions = array_values(array_filter(
            $permissions,
            fn ($name) => $name !== 'driver.panel'
        ));

        if (count($adminPermissions) > 0) {
            return $next($request);
        }

        if (in_array('driver.panel', $permissions, true) || $user->driver) {
            return redirect()->route('driver.dashboard');
        }

        abort(403, 'Sem permissão para acessar o painel administrativo.');
    }
}
