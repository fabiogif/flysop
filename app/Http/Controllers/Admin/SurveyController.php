<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateSurvey;
use App\Models\Survey;
use App\Services\SurveyService;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    public function __construct(
        protected SurveyService $surveyService
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

    public function responses($id)
    {
        $survey = $this->findTenantSurvey($id);
        if (!$survey) {
            return redirect()->route('surveys.index');
        }

        $survey->load('questions');
        $responses = $survey->responses()
            ->with('answers')
            ->paginate(20);
        $summary = $this->surveyService->summarize($survey);

        return view('admin.pages.surveys.responses', compact('survey', 'responses', 'summary'));
    }

    /**
     * Respostas em PDF para impressão — mesmo padrão síncrono de OccurrencesController::pdf(),
     * aberto inline (stream) para o navegador já oferecer imprimir.
     * ponytail: carrega todas as respostas de uma vez; se uma pesquisa passar de alguns
     * milhares de respostas, mover para a fila (ver App\Jobs\GenerateReportJob).
     */
    public function responsesPdf($id)
    {
        $survey = $this->findTenantSurvey($id);
        if (!$survey) {
            return redirect()->route('surveys.index');
        }

        $survey->load('questions');
        $responses = $survey->responses()->with('answers')->get();
        $summary = $this->surveyService->summarize($survey);

        return Pdf::loadView('pdf.survey-responses', compact('survey', 'responses', 'summary'))
            ->stream('respostas-pesquisa-' . $survey->id . '.pdf');
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

    private function findTenantSurvey($id): ?Survey
    {
        return Survey::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('id', $id)
            ->first();
    }
}
