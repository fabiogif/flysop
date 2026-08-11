<?php

use App\Models\Occurrences;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * Canal privado por ocorrência: rota do motorista em tempo real.
 * Só pode escutar quem tem permissão para ver ocorrências no admin (occurrences)
 * ou o motorista atualmente atribuído àquela ocorrência.
 */
Broadcast::channel('occurrence.{id}', function ($user, $id) {
    if (! $user) {
        return false;
    }

    $occurrence = Occurrences::find($id);
    if (! $occurrence) {
        return false;
    }

    if ($user->isAdmin() || $user->hasPermission('occurrences')) {
        return true;
    }

    return $user->driver && (int) $occurrence->driver_id === (int) $user->driver->id;
});

/**
 * Canal privado do dashboard (Fase 4): notifica qualquer criação/mudança de status de
 * ocorrência, para trocar o polling de 60s por atualização imediata. Mesma regra de
 * acesso do módulo de ocorrências no admin (sem checagem de motorista — não é usado
 * no painel do motorista).
 */
Broadcast::channel('occurrences-dashboard', function ($user) {
    return $user && ($user->isAdmin() || $user->hasPermission('occurrences'));
});
