<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateOccurrences;
use App\Models\DriverPosition;
use App\Services\OccurrenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class OccurrencesController extends Controller
{
    public function __construct(
        protected OccurrenceService $occurrenceService
    ) {
    }

    public function index(): View
    {
        $occurrences = $this->occurrenceService->getPaginatedList();

        return view('admin.pages.occurrences.index', ['occurrences' => $occurrences]);
    }

    public function create(): View
    {
        $formData = $this->occurrenceService->getFormData();

        return view('admin.pages.occurrences.create', $formData);
    }

    public function store(StoreUpdateOccurrences $request): RedirectResponse
    {
        $anexos = $request->hasFile('anexo') ? $request->allFiles()['anexo'] : [];
        $this->occurrenceService->storeForAdmin($request->validated(), is_array($anexos) ? $anexos : [$anexos]);

        return redirect()->route('occurrences.index')->with('messageSuccess', 'Ocorrência cadastrada com sucesso.');
    }

    public function show(int $id): View|RedirectResponse
    {
        $occurrence = $this->occurrenceService->findOrFail($id);
        $occurrence->load('imagens');
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
        $formData = $this->occurrenceService->getFormData();

        return view('admin.pages.occurrences.edit', [
            'occurrences' => $occurrence,
            'typeOccurrences' => $formData['typeOccurrences'],
            'statusOccurrences' => $formData['statusOccurrences'],
            'issuings' => $formData['issuings'],
        ]);
    }

    public function update(StoreUpdateOccurrences $request, int $id): RedirectResponse
    {
        $anexos = $request->hasFile('anexo') ? $request->allFiles()['anexo'] : [];
        $this->occurrenceService->updateForAdmin($id, $request->validated(), is_array($anexos) ? $anexos : [$anexos]);

        return redirect()->route('occurrences.index')->with('messageSuccess', 'Ocorrência atualizada com sucesso.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->occurrenceService->deleteForAdmin($id);

        return redirect()->route('occurrences.index')->with('messageSuccess', 'Excluído com sucesso');
    }

    public function search(Request $request): View
    {
        $filters = $request->all();
        $occurrences = $this->occurrenceService->search($request->get('filter'));

        return view('admin.pages.occurrences.index', [
            'occurrences' => $occurrences,
            'filters' => $filters,
        ]);
    }

    /**
     * Retorna a rota do motorista para a ocorrência (posições ordenadas) e última posição.
     * Usado no mapa da tela de detalhe da ocorrência para exibir polyline e marcador do motorista.
     */
    public function driverRoute(int $id): JsonResponse
    {
        $occurrence = $this->occurrenceService->findOrFail($id);
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
