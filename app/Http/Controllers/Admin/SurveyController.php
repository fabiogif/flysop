<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateSurvey;
use App\Models\Survey;
use App\Services\AuditLogger;
use App\Services\SurveyService;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    public function __construct(
        protected SurveyService $surveyService,
        protected AuditLogger $audit
    ) {
        $this->middleware(['can:surveys']);
    }

    public function index()
    {
        $surveys = Survey::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->withCount(['questions', 'responses'])
            ->orderByDesc('updated_at')
            ->paginate();

        return view('admin.pages.surveys.index', compact('surveys'));
    }

    public function create()
    {
        return view('admin.pages.surveys.create');
    }

    public function store(StoreUpdateSurvey $request)
    {
        $survey = $this->surveyService->createSurvey($request->validated(), $request->user());

        return redirect()
            ->route('surveys.show', $survey->id)
            ->with('messageSuccess', 'Pesquisa criada com sucesso.');
    }

    public function show($id)
    {
        $survey = $this->findTenantSurvey($id);
        if (!$survey) {
            return redirect()->route('surveys.index');
        }

        $survey->load(['questions']);
        $survey->loadCount('responses');
        $recentResponses = $survey->responses()->withCount('answers')->limit(10)->get();

        return view('admin.pages.surveys.show', [
            'survey' => $survey,
            'recentResponses' => $recentResponses,
        ]);
    }

    public function edit($id)
    {
        $survey = $this->findTenantSurvey($id);
        if (!$survey) {
            return redirect()->route('surveys.index');
        }

        $survey->load('questions');

        return view('admin.pages.surveys.edit', compact('survey'));
    }

    public function update(StoreUpdateSurvey $request, $id)
    {
        $survey = $this->findTenantSurvey($id);
        if (!$survey) {
            return redirect()->route('surveys.index');
        }

        $this->surveyService->updateSurvey($survey, $request->validated());

        return redirect()
            ->route('surveys.show', $survey->id)
            ->with('messageSuccess', 'Pesquisa atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $survey = $this->findTenantSurvey($id);
        if (!$survey) {
            return redirect()->route('surveys.index');
        }

        $survey->delete();

        return redirect()
            ->route('surveys.index')
            ->with('messageSuccess', 'Pesquisa excluída com sucesso.');
    }

    public function search(Request $request)
    {
        $filters = $request->only('filter');
        $surveys = Survey::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->withCount(['questions', 'responses'])
            ->when($request->filter, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('title', 'ILIKE', '%' . $request->filter . '%')
                        ->orWhere('description', 'ILIKE', '%' . $request->filter . '%');
                });
            })
            ->orderByDesc('updated_at')
            ->paginate();

        return view('admin.pages.surveys.index', compact('surveys', 'filters'));
    }

    public function toggle($id)
    {
        $survey = $this->findTenantSurvey($id);
        if (!$survey) {
            return redirect()->route('surveys.index');
        }

        $this->surveyService->toggleActive($survey);

        return redirect()
            ->back()
            ->with('messageSuccess', $survey->fresh()->is_active ? 'Pesquisa ativada.' : 'Pesquisa desativada.');
    }

    public function responses(Request $request, $id)
    {
        $survey = $this->findTenantSurvey($id);
        if (!$survey) {
            return redirect()->route('surveys.index');
        }

        $period = $this->validatedPeriod($request);
        $survey->load('questions');
        $responses = $this->surveyService->responsesQuery($survey, $period)
            ->with('answers')
            ->paginate(20)
            ->withQueryString();
        $summary = $this->surveyService->summarize($survey, $period);

        return view('admin.pages.surveys.responses', compact('survey', 'responses', 'summary', 'period'));
    }

    /**
     * Respostas em PDF para impressão — mesmo padrão síncrono de OccurrencesController::pdf(),
     * aberto inline (stream) para o navegador já oferecer imprimir. Respeita o período filtrado.
     * ponytail: carrega todas as respostas do período de uma vez; se uma pesquisa passar de
     * alguns milhares de respostas, mover para a fila (ver App\Jobs\GenerateReportJob).
     */
    public function responsesPdf(Request $request, $id)
    {
        $survey = $this->findTenantSurvey($id);
        if (!$survey) {
            return redirect()->route('surveys.index');
        }

        $period = $this->validatedPeriod($request);
        $survey->load('questions');
        $responses = $this->surveyService->responsesQuery($survey, $period)->with('answers')->get();
        $summary = $this->surveyService->summarize($survey, $period);

        return Pdf::loadView('pdf.survey-responses', compact('survey', 'responses', 'summary', 'period'))
            ->stream('respostas-pesquisa-' . $survey->id . '.pdf');
    }

    /**
     * Impressão de uma única resposta — mesma view do PDF geral, sem o resumo.
     */
    public function responsePdf($id, $responseId)
    {
        $survey = $this->findTenantSurvey($id);
        if (!$survey) {
            return redirect()->route('surveys.index');
        }

        $survey->load('questions');
        $responses = $survey->responses()->with('answers')->where('id', $responseId)->get();
        if ($responses->isEmpty()) {
            return redirect()->route('surveys.responses', $survey->id);
        }

        return Pdf::loadView('pdf.survey-responses', ['survey' => $survey, 'responses' => $responses, 'summary' => null])
            ->stream('resposta-' . $responses->first()->id . '-pesquisa-' . $survey->id . '.pdf');
    }

    public function qrcode($id)
    {
        $survey = $this->findTenantSurvey($id);
        if (!$survey) {
            return redirect()->route('surveys.index');
        }

        $result = Builder::create()
            ->data($survey->publicUrl())
            ->size(300)
            ->margin(10)
            ->build();

        return response($result->getString(), 200)->header('Content-Type', $result->getMimeType());
    }

    /**
     * Exclui uma resposta (spam/teste). As answers saem pelo cascade da FK.
     */
    public function destroyResponse($id, $responseId)
    {
        $survey = $this->findTenantSurvey($id);
        if (!$survey) {
            return redirect()->route('surveys.index');
        }

        $response = $survey->responses()->withCount('answers')->where('id', $responseId)->first();
        if (!$response) {
            return redirect()->route('surveys.responses', $survey->id);
        }

        $response->delete();

        // Subject é a pesquisa (a resposta deixa de existir). Sem IP nem texto das respostas
        // no log: são dados do cidadão e o log não deve virar cópia do que foi apagado.
        $this->audit->log('survey.response_deleted', $survey, [
            'survey_response_id' => $response->id,
            'submitted_at' => $response->submitted_at?->toIso8601String(),
            'answers_count' => $response->answers_count,
        ]);

        return redirect()
            ->back()
            ->with('messageSuccess', 'Resposta excluída com sucesso.');
    }

    private function validatedPeriod(Request $request): array
    {
        return $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);
    }

    private function findTenantSurvey($id): ?Survey
    {
        return Survey::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('id', $id)
            ->first();
    }
}
