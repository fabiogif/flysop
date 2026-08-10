<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/* |-------------------------------------------------------------------------- | API Routes |-------------------------------------------------------------------------- | | Here is where you can register API routes for your application. These | routes are loaded by the RouteServiceProvider within a group which | is assigned the "api" middleware group. Enjoy building your API! | */
Route::post('/sanctum/token', [App\Http\Controllers\Api\Auth\AuthClientController::class, 'auth']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/auth/me', [App\Http\Controllers\Api\Auth\AuthClientController::class, 'me']);
    Route::post('/auth/logout', [App\Http\Controllers\Api\Auth\AuthClientController::class, 'logout']);
});


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/client/{id}', [App\Http\Controllers\Api\Auth\RegisterController::class, 'show']);

Route::post('/client', [App\Http\Controllers\Api\Auth\RegisterController::class, 'store']);
Route::post('/clientConsumer', [App\Http\Controllers\Api\Auth\RegisterController::class, 'storeConsumer']);

//Route::post('/occurrences', [App\Http\Controllers\Api\OccurrenceApiController::class , 'store']);

Route::post('/occurrences', [App\Http\Controllers\Api\OccurrenceApiController::class, 'createNewOccurrence']);

/*
|--------------------------------------------------------------------------
| Rotas autenticadas (auth:sanctum)
|--------------------------------------------------------------------------
| Antes sem middleware de auth (gap documentado em docs/specs/security.md) —
| listavam ocorrências e empresas publicamente. O endpoint de posição do
| motorista (Api\Driver\DriverLocationController) foi removido: era uma
| duplicata insegura de Driver\DriverPositionController (driver_id vinha do
| payload, sem autenticação) e não havia nenhum cliente externo dependendo
| dele; a gravação de posição agora tem um único ponto de verdade em
| App\Services\DriverPositionService, usado pela rota web autenticada.
*/
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/tenants/{uuid}', [App\Http\Controllers\Api\TenantApiController::class, 'show']);
    Route::get('/tenants', [App\Http\Controllers\Api\TenantApiController::class, 'index']);

    Route::get('/occurrences', [App\Http\Controllers\Api\OccurrenceApiController::class, 'index']);
    Route::get('/occurrences/{uuid}', [App\Http\Controllers\Api\OccurrenceApiController::class, 'show']);
    Route::get('/occurrences/getOccurrenceByClientId/{clientId}', [App\Http\Controllers\Api\OccurrenceApiController::class, 'getOccurrenceByClientId']);

    Route::get('/typeOccurrence', [App\Http\Controllers\Api\TypeOccurrenceApiController::class, 'index']);
});
