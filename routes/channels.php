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
 * Apenas usuários autenticados que podem ver a ocorrência (admin) podem escutar.
 */
Broadcast::channel('occurrence.{id}', function ($user, $id) {
    if (! $user) {
        return false;
    }
    $occurrence = Occurrences::find($id);

    return $occurrence !== null;
});
