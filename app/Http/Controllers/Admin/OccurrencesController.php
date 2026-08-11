<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateOccurrences;
use App\Models\DriverPosition;
use App\Models\Occurrences;
use App\Services\OccurrenceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class OccurrencesController extends Controller
{
    public function __construct(
        protected OccurrenceService $occurrenceService
    ) {
        $this->middleware(['can:occurrences']);
    }

    public function index(): View
    {
        $this->authorize('viewAny', Occurrences::class);

        $occurrences = $this->occurrenceService->getPaginatedList();

        return view('admin.pages.occurrences.index', ['occurrences' => $occurrences]);
    }

    public function create(): View
    {
        $this->authorize('create', Occurrences::class);

        $formData = $this->occurrenceService->getFormData();

        return view('admin.pages.occurrences.create', $formData);
    }

    public function store(StoreUpdateOccurrences $request): RedirectResponse
    {
        $this->authorize('create', Occurrences::class);

        $anexos = $request->hasFile('anexo') ? $request->allFiles()['anexo'] : [];
        $this->occurrenceService->storeForAdmin(
            $request->validated(),
            is_array($anexos) ? $anexos : [$anexos],
            $request->input('anexo_phase')
        );

        return redirect()->route('occurrences.index')->with('messageSuccess', 'Ocorrência cadastrada com sucesso.');
    }

    public function show(int $id): View|RedirectResponse
    {
        $occurrence = $this->occurrenceService->findOrFail($id);
        $this->authorize('view', $occurrence);

        // Log de acesso a dados sensíveis (LGPD, Fase 6) — log separado ('lgpd_access') do
        // de auditoria de campos alterados, para não misturar "quem viu" com "quem editou".
        activity('lgpd_access')->causedBy(auth()->user())->performedOn($occurrence)->log('Visualizou dados da ocorrência.');

        $occurrence->load([
            'imagens.uploadedBy',
            'priority',
            'statusOccurence',
            'typeOccurrence',
            'issuing',
            'driver',
            'statusHistory.fromStatus',
            'statusHistory.toStatus',
            'statusHistory.changedBy',
            'activities.causer',
            'possibleDuplicateOf',
        ]);
        $formData = $this->occurrenceService->getFormData();

        return view('admin.pages.occurrences.show', [
            'occurrences' => $occurrence,
            'occurrencesImagens' => $occurrence->imagens,
            'typeOccurrences' => $formData['typeOccurrences'],
            'statusOccurrences' => $formData['statusOccurrences'],
            'issuings' => $formData['issuings'],
        ]);
    }

    public function edit(int $id): View|RedirectResponse
    {
        $occurrence = $this->occurrenceService->findOrFail($id);
        $this->authorize('update', $occurrence);

        $formData = $this->occurrenceService->getFormData();

        return view('admin.pages.occurrences.edit', [
            'occurrences' => $occurrence,
            'typeOccurrences' => $formData['typeOccurrences'],
            'statusOccurrences' => $formData['statusOccurrences'],
            'issuings' => $formData['issuings'],
            'drivers' => $formData['drivers'],
            'priorities' => $formData['priorities'],
        ]);
    }

    public function update(StoreUpdateOccurrences $request, int $id): RedirectResponse
    {
        $occurrence = $this->occurrenceService->findOrFail($id);
        $this->authorize('update', $occurrence);

        $anexos = $request->hasFile('anexo') ? $request->allFiles()['anexo'] : [];
        $this->occurrenceService->updateForAdmin(
            $id,
            $request->validated(),
            is_array($anexos) ? $anexos : [$anexos],
            $request->input('anexo_phase')
        );

        return redirect()->route('occurrences.index')->with('messageSuccess', 'Ocorrência atualizada com sucesso.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $occurrence = $this->occurrenceService->findOrFail($id);
        $this->authorize('delete', $occurrence);

        $this->occurrenceService->deleteForAdmin($id);

        return redirect()->route('occurrences.index')->with('messageSuccess', 'Excluído com sucesso');
    }

    public function search(Request $request): View
    {
        $this->authorize('viewAny', Occurrences::class);

        $filters = $request->all();
        $occurrences = $this->occurrenceService->search($request->get('filter'));

        return view('admin.pages.occurrences.index', [
            'occurrences' => $occurrences,
            'filters' => $filters,
        ]);
    }

    /**
     * Ficha da ocorrência em PDF (Fase 6) — síncrono: é um único registro, gerar via fila
     * seria complexidade desnecessária (ver App\Jobs\GenerateReportJob para os relatórios
     * em lote, esses sim assíncronos).
     */
    public function pdf(int $id): Response
    {
        $occurrence = $this->occurrenceService->findOrFail($id);
        $this->authorize('view', $occurrence);

        $occurrence->load(['statusOccurence', 'typeOccurrence', 'priority', 'driver', 'statusHistory.fromStatus', 'statusHistory.toStatus', 'statusHistory.changedBy']);

        $pdf = Pdf::loadView('pdf.occurrence', ['occurrence' => $occurrence]);

        return $pdf->download('ficha-' . ($occurrence->protocol ?? $occurrence->id) . '.pdf');
    }

    /**
     * Confirma que não é duplicata (Fase 6) — só limpa a sugestão automática.
     */
    public function dismissDuplicate(int $id): RedirectResponse
    {
        $occurrence = $this->occurrenceService->findOrFail($id);
        $this->authorize('update', $occurrence);

        $this->occurrenceService->dismissDuplicateFlag($id);

        return redirect()->route('occurrences.show', $id)->with('messageSuccess', 'Marcada como não duplicata.');
    }

    /**
     * Direito ao esquecimento (LGPD, Fase 6) — irreversível.
     */
    public function forget(int $id): RedirectResponse
    {
        $occurrence = $this->occurrenceService->findOrFail($id);
        $this->authorize('forget', $occurrence);

        $this->occurrenceService->forgetPersonalData($id);

        return redirect()->route('occurrences.show', $id)->with('messageSuccess', 'Dados pessoais removidos.');
    }

    /**
     * Retorna a rota do motorista para a ocorrência (posições ordenadas) e última posição.
     * Usado no mapa da tela de detalhe da ocorrência para exibir polyline e marcador do motorista.
     */
    public function driverRoute(int $id): JsonResponse
    {
        $occurrence = $this->occurrenceService->findOrFail($id);
        $this->authorize('view', $occurrence);

        $occurrence->load('driver');

        $positions = DriverPosition::forOccurrence($id)
            ->recent(120)
            ->orderBy('created_at')
            ->limit(200)
            ->get();

        $route = $positions->map(fn ($p) => [
            'lat' => (float) $p->latitude,
            'lng' => (float) $p->longitude,
            'created_at' => $p->created_at?->toIso8601String(),
        ])->values()->all();

        $last = $positions->last();

        return response()->json([
            'route' => $route,
            'last_position' => $last ? [
                'lat' => (float) $last->latitude,
                'lng' => (float) $last->longitude,
                'created_at' => $last->created_at?->toIso8601String(),
            ] : null,
            'driver' => $occurrence->driver_id && $occurrence->driver ? [
                'id' => $occurrence->driver->id,
                'name' => $occurrence->driver->name,
            ] : null,
        ]);
    }
}
