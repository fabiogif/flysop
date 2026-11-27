<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSurveyResponse;
use App\Http\Resources\SurveyResource;
use App\Models\Client;
use App\Models\Survey;
use App\Models\Tenant;
use App\Services\SurveyService;
use Illuminate\Http\Request;

class SurveyApiController extends Controller
{
    public function __construct(
        protected SurveyService $surveyService
    ) {
    }

    /**
     * Pesquisas ativas disponíveis para o cidadão responder (app/site público). Mesmo
     * default de tenant único usado em ClientService — sem multi-tenant expandido para
     * rota autenticada por Client (não por User admin).
     */
    public function index(Request $request)
    {
        $tenantId = Tenant::query()->value('id') ?? 1;

        $surveys = Survey::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->get();

        // "answered" para o app esconder/bloquear a pesquisa já respondida por este cidadão/aparelho
        $answered = $this->surveyService->answeredSurveyIds($this->respondentKey($request));
        $surveys->each(fn (Survey $s) => $s->setAttribute('answered', in_array($s->id, $answered, true)));

        return SurveyResource::collection($surveys);
    }

    /**
     * Cidadão logado (token Sanctum opcional — a rota é pública) vale por todos os aparelhos;
     * sem login, o id aleatório que o app guarda no aparelho (?device_id= ou no corpo).
     */
    private function respondentKey(Request $request): ?string
    {
        $user = $request->user('sanctum');
        if ($user instanceof Client) {
            return $this->surveyService->respondentKey("client:{$user->id}");
        }

        $deviceId = $request->input('device_id');

        return is_string($deviceId) && $deviceId !== '' ? $this->surveyService->respondentKey('device:'.mb_substr($deviceId, 0, 64)) : null;
    }

    public function show(string $token)
    {
        $survey = $this->surveyService->findActiveByToken($token);

        if (!$survey) {
            return response()->json(['message' => 'Pesquisa não encontrada.'], 404);
        }

        return new SurveyResource($survey);
    }

    public function store(StoreSurveyResponse $request, string $token)
    {
        $survey = $request->survey();

        if (!$survey) {
            return response()->json(['message' => 'Pesquisa não encontrada.'], 404);
        }

        if (!$survey->is_active) {
            return response()->json(['message' => 'Esta pesquisa está encerrada.'], 422);
        }

        $respondentKey = $this->respondentKey($request);
        if ($this->surveyService->hasResponded($survey, $respondentKey)) {
            return response()->json(['message' => SurveyService::ALREADY_ANSWERED], 422);
        }

        $response = $this->surveyService->submitResponse($survey, $request->input('answers', []), [
            'ip' => $request->ip(),
            'justifications' => $request->input('justifications', []),
            'respondent_name' => $request->input('respondent_name'),
            'respondent_key' => $respondentKey,
        ]);

        return response()->json(['id' => $response->id], 201);
    }
}
