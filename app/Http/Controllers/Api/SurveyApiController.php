<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSurveyResponse;
use App\Http\Resources\SurveyResource;
use App\Models\Survey;
use App\Models\Tenant;
use App\Services\SurveyService;

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
    public function index()
    {
        $tenantId = Tenant::query()->value('id') ?? 1;

        $surveys = Survey::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->get();

        return SurveyResource::collection($surveys);
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

        $response = $this->surveyService->submitResponse(
            $survey,
            $request->input('answers', []),
            $request->ip()
        );

        return response()->json(['id' => $response->id], 201);
    }
}
