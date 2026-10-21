<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/* |-------------------------------------------------------------------------- | API Routes |-------------------------------------------------------------------------- | | Here is where you can register API routes for your application. These | routes are loaded by the RouteServiceProvider within a group which | is assigned the "api" middleware group. Enjoy building your API! | */
Route::post('/sanctum/token', [App\Http\Controllers\Api\Auth\AuthClientController::class, 'auth']);

// Login leve do cidadão (app/site público): nome + celular obrigatórios, sem senha —
// ver análise de impacto em docs/specs/flysop.md e app/Services/ClientService.php.
Route::post('/auth/citizen', [App\Http\Controllers\Api\Auth\CitizenAuthController::class, 'auth']);
Route::post('/auth/register', [App\Http\Controllers\Api\Auth\CitizenRegisterController::class, 'register']);
Route::post('/auth/social', [App\Http\Controllers\Api\Auth\CitizenRegisterController::class, 'socialAuth']);
Route::get('/profile/{username}', [App\Http\Controllers\Api\ProfileApiController::class, 'show']);

// Login do gestor de órgão no app (público por ser o login; throttle contra força bruta).
Route::post('/auth/staff/login', [App\Http\Controllers\Api\Auth\StaffAuthController::class, 'login'])->middleware('throttle:5,1');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/auth/me', [App\Http\Controllers\Api\Auth\AuthClientController::class, 'me']);
    Route::post('/auth/logout', [App\Http\Controllers\Api\Auth\AuthClientController::class, 'logout']);
});

// Cidadão (Client): token de gestor não entra aqui.
Route::middleware(['auth:sanctum', 'ensure.citizen'])->group(function () {
    Route::put('/profile', [App\Http\Controllers\Api\ProfileApiController::class, 'update']);
    Route::post('/profile/avatar', [App\Http\Controllers\Api\ProfileApiController::class, 'uploadAvatar']);
    Route::get('/my-occurrences', [App\Http\Controllers\Api\OccurrenceApiController::class, 'myOccurrences']);
});

// Gestor de órgão (User com issuing_id): ocorrências do órgão + feed de notificações.
Route::middleware(['auth:sanctum', 'ensure.manager'])->prefix('manager')->group(function () {
    Route::get('/occurrences', [App\Http\Controllers\Api\ManagerOccurrenceApiController::class, 'index']);
    Route::get('/occurrences/{id}', [App\Http\Controllers\Api\ManagerOccurrenceApiController::class, 'show']);
    Route::patch('/occurrences/{id}/status', [App\Http\Controllers\Api\ManagerOccurrenceApiController::class, 'updateStatus']);
    Route::get('/status-occurrences', [App\Http\Controllers\Api\ManagerOccurrenceApiController::class, 'statuses']);

    Route::get('/notifications', [App\Http\Controllers\Api\NotificationApiController::class, 'index']);
    Route::post('/notifications/read-all', [App\Http\Controllers\Api\NotificationApiController::class, 'readAll']);
    Route::patch('/notifications/{id}/read', [App\Http\Controllers\Api\NotificationApiController::class, 'read']);
    Route::delete('/notifications/{id}', [App\Http\Controllers\Api\NotificationApiController::class, 'destroy']);
    Route::put('/notification-settings', [App\Http\Controllers\Api\NotificationApiController::class, 'updateSettings']);
});


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/client/{id}', [App\Http\Controllers\Api\Auth\RegisterController::class, 'show']);

Route::post('/client', [App\Http\Controllers\Api\Auth\RegisterController::class, 'store']);
Route::post('/clientConsumer', [App\Http\Controllers\Api\Auth\RegisterController::class, 'storeConsumer']);

//Route::post('/occurrences', [App\Http\Controllers\Api\OccurrenceApiController::class , 'store']);

Route::post('/occurrences', [App\Http\Controllers\Api\OccurrenceApiController::class, 'createNewOccurrence']);

// Públicas para quem usa o app sem cadastro (ocorrência anônima): tipos para o formulário,
// mapa e consulta por protocolo — sem dados pessoais (PublicOccurrenceResource); o autor
// logado recebe o detalhe completo pelo token, se mandar.
Route::get('/typeOccurrence', [App\Http\Controllers\Api\TypeOccurrenceApiController::class, 'index']);
Route::get('/occurrences', [App\Http\Controllers\Api\OccurrenceApiController::class, 'index']);
Route::get('/occurrences/protocol/{protocol}', [App\Http\Controllers\Api\OccurrenceApiController::class, 'showByProtocol']);
Route::get('/occurrences/{id}', [App\Http\Controllers\Api\OccurrenceApiController::class, 'show'])->whereNumber('id');

// Pesquisas: públicas como no web (PublicSurveyController, link/QR sem login); a resposta grava
// só o IP, nunca o cidadão. Reaproveita SurveyService/StoreSurveyResponse sem duplicar regra.
Route::get('/surveys', [App\Http\Controllers\Api\SurveyApiController::class, 'index']);
Route::get('/surveys/{token}', [App\Http\Controllers\Api\SurveyApiController::class, 'show']);
Route::post('/surveys/{token}/responses', [App\Http\Controllers\Api\SurveyApiController::class, 'store']);

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

    Route::get('/occurrences/getOccurrenceByClientId/{clientId}', [App\Http\Controllers\Api\OccurrenceApiController::class, 'getOccurrenceByClientId']);
});
