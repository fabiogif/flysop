<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OccurrenceResource;
use App\Http\Resources\StatusOccurenceResource;
use App\Models\StatusOccurrence;
use App\Services\OccurrenceService;
use Illuminate\Http\Request;

/**
 * App do gestor de órgão: ocorrências do próprio órgão (escopo garantido pela OccurrencePolicy).
 */
class ManagerOccurrenceApiController extends Controller
{
    public function __construct(protected OccurrenceService $occurrenceService)
    {
    }

    public function index(Request $request)
    {
        $request->validate(['status_occurrences_id' => 'nullable|integer|exists:status_occurrences,id']);

        $occurrences = $this->occurrenceService->getOccurrencesForIssuing(
            $request->user()->issuing_id,
            (int) $request->get('status_occurrences_id') ?: null
        );

        return OccurrenceResource::collection($occurrences);
    }

    public function show(int $id)
    {
        $occurrence = $this->occurrenceService->findOrFail($id);
        $this->authorize('view', $occurrence);

        // Mesmo log de acesso a dados sensíveis do painel (LGPD) — ver Admin\OccurrencesController::show().
        activity('lgpd_access')->causedBy(auth()->user())->performedOn($occurrence)->log('Visualizou dados da ocorrência (app do gestor).');

        $occurrence->load(['statusOccurence', 'typeOccurrence', 'client', 'statusHistory.toStatus', 'statusHistory.changedBy']);
        $occurrence->anexo = $occurrence->imagens()->pluck('url')->all();

        return (new OccurrenceResource($occurrence))->additional([
            // Pelo app o cidadão não preenche nome/e-mail na ocorrência: vêm da conta dele.
            // Anônima: o órgão não sabe quem abriu.
            'requester' => $occurrence->is_anonymous ? null : [
                'name' => $occurrence->name ?: $occurrence->client?->name,
                'email' => $occurrence->email ?: $occurrence->client?->email,
                'phone' => $occurrence->phone ?: $occurrence->client?->phone,
            ],
            'history' => $occurrence->statusHistory->map(fn ($entry) => [
                'status' => $entry->toStatus?->name,
                'note' => $entry->note,
                'changed_by' => $entry->changedBy?->name,
                'created_at' => $entry->created_at->toIso8601String(),
            ]),
        ]);
    }

    public function updateStatus(Request $request, int $id)
    {
        $data = $request->validate(['status_occurrences_id' => 'required|integer|exists:status_occurrences,id']);

        $this->authorize('update', $this->occurrenceService->findOrFail($id));

        $occurrence = $this->occurrenceService->updateStatusByAdmin($id, $data['status_occurrences_id']);

        return new OccurrenceResource($occurrence->load(['statusOccurence', 'typeOccurrence']));
    }

    public function statuses()
    {
        return StatusOccurenceResource::collection(StatusOccurrence::orderBy('sort_order')->get());
    }
}
