<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Driver;
use App\Models\Issuing;
use App\Models\OccurrenceStatusHistory;
use App\Models\Occurrences;
use App\Models\Priority;
use App\Models\StatusOccurrence;
use App\Models\TypeOccurrence;
use App\Repositories\Contracts\OccurrenceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class OccurrenceService
{
    public function __construct(
        protected OccurrenceRepositoryInterface $repository
    ) {
    }

    public function getAllOccurrences(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getAllOccurrences($perPage);
    }

    public function getOccurrenceById(string $id): ?Model
    {
        return $this->repository->getOccurrenceById($id);
    }

    public function getOccurrenceByClientId(int $clientId): LengthAwarePaginator
    {
        return $this->repository->getOccurrenceByClientId($clientId);
    }

    public function createNewOccurrence(array $data): Model
    {
        return $this->createOccurrenceWithDefaults($data);
    }

    /**
     * Lista paginada para o admin.
     */
    public function getPaginatedList(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getPaginatedList($perPage);
    }

    /**
     * Dados para formulário (tipos, status, órgãos).
     */
    public function getFormData(): array
    {
        $tenantId = auth()->user()->tenant_id ?? null;

        return [
            'typeOccurrences' => TypeOccurrence::orderBy('name')->get(),
            'statusOccurrences' => StatusOccurrence::orderBy('name')->get(),
            'issuings' => Issuing::orderBy('name')->get(),
            'drivers' => $tenantId ? Driver::where('tenant_id', $tenantId)->orderBy('name')->get() : collect(),
            'priorities' => Priority::orderBy('weight', 'desc')->get(),
        ];
    }

    public function findOrFail(int $id): Occurrences
    {
        $occurrence = $this->repository->find($id);
        if (! $occurrence) {
            abort(404);
        }

        return $occurrence;
    }

    /**
     * Cria ocorrência pelo painel admin (com anexos).
     */
    public function storeForAdmin(array $data, array $anexos = []): Occurrences
    {
        $data['users_id'] = auth()->id();
        if (empty($data['clients_id'])) {
            $tenantId = auth()->user()->tenant_id ?? null;
            $data['clients_id'] = $tenantId
                ? (Client::where('tenant_id', $tenantId)->first()?->id ?? 1)
                : 1;
        }

        $occurrence = $this->createOccurrenceWithDefaults($data);

        foreach ($anexos as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $url = $file->store('occurrence/occurrences');
                $occurrence->imagens()->create(['url' => $url]);
            }
        }

        return $occurrence;
    }

    /**
     * Ponto único de criação de ocorrência (admin ou API): gera protocolo, calcula due_at
     * a partir do SLA e registra a entrada inicial no histórico de status.
     */
    private function createOccurrenceWithDefaults(array $data): Occurrences
    {
        $data['protocol'] = $this->generateProtocol();
        $data['due_at'] = $this->calculateDueAt($data['priority_id'] ?? null, $data['type_occurrences_id'] ?? null);

        $occurrence = $this->repository->createNewOccurrence($data);

        OccurrenceStatusHistory::create([
            'occurrence_id' => $occurrence->id,
            'from_status_id' => null,
            'to_status_id' => $occurrence->status_occurrences_id,
            'changed_by_user_id' => auth()->id(),
            'note' => 'Ocorrência registrada.',
        ]);

        return $occurrence;
    }

    /**
     * Atualiza ocorrência pelo painel admin (anexos opcionais).
     */
    public function updateForAdmin(int $id, array $data, array $anexos = []): void
    {
        $occurrence = $this->findOrFail($id);

        if (! empty($data['driver_id']) && (int) $occurrence->driver_id !== (int) $data['driver_id']) {
            $statusAguardando = StatusOccurrence::where('name', 'Aguardando aceitação')->first();
            if ($statusAguardando) {
                $data['status_occurrences_id'] = $statusAguardando->id;
            }
        }

        $newStatusId = isset($data['status_occurrences_id']) ? (int) $data['status_occurrences_id'] : null;
        unset($data['status_occurrences_id']);

        if (array_key_exists('priority_id', $data) || array_key_exists('type_occurrences_id', $data)) {
            $data['due_at'] = $this->calculateDueAt(
                $data['priority_id'] ?? $occurrence->priority_id,
                $data['type_occurrences_id'] ?? $occurrence->type_occurrences_id
            );
        }

        if (! empty($data)) {
            $occurrence->update($data);
        }

        if ($newStatusId !== null) {
            $this->recordStatusChange($occurrence, $newStatusId);
        }

        foreach ($anexos as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $url = $file->store('occurrence/occurrences');
                $occurrence->imagens()->create(['url' => $url]);
            }
        }
    }

    /**
     * Gera o protocolo sequencial da ocorrência no formato OC-{ano}-{sequencial}.
     * Simplificação aceitável para o volume atual (painel único, sem concorrência alta);
     * não usa lock/sequência dedicada.
     */
    private function generateProtocol(): string
    {
        $year = now()->year;
        $count = Occurrences::whereYear('created_at', $year)->count();

        return sprintf('OC-%d-%05d', $year, $count + 1);
    }

    /**
     * Prazo (due_at) a partir do SLA: prioridade tem precedência sobre o SLA do tipo de ocorrência.
     */
    private function calculateDueAt($priorityId, $typeOccurrenceId): ?Carbon
    {
        $hours = null;

        if ($priorityId) {
            $hours = Priority::find($priorityId)?->default_sla_hours;
        }

        if (! $hours && $typeOccurrenceId) {
            $hours = TypeOccurrence::find($typeOccurrenceId)?->sla_hours;
        }

        return $hours ? now()->addHours($hours) : null;
    }

    /**
     * Ponto único de mudança de status: atualiza a ocorrência e grava o histórico
     * (occurrence_status_history). Nenhuma outra rotina deve escrever em
     * status_occurrences_id diretamente.
     */
    private function recordStatusChange(Occurrences $occurrence, int $newStatusId, ?string $note = null): Occurrences
    {
        $fromStatusId = $occurrence->status_occurrences_id;

        if ((int) $fromStatusId === $newStatusId) {
            return $occurrence;
        }

        $occurrence->update(['status_occurrences_id' => $newStatusId]);

        OccurrenceStatusHistory::create([
            'occurrence_id' => $occurrence->id,
            'from_status_id' => $fromStatusId,
            'to_status_id' => $newStatusId,
            'changed_by_user_id' => auth()->id(),
            'note' => $note,
        ]);

        return $occurrence->fresh();
    }

    /**
     * Remove ocorrência (e imagens do disco se desejado).
     */
    public function deleteForAdmin(int $id): void
    {
        $occurrence = $this->findOrFail($id);
        foreach ($occurrence->imagens as $img) {
            if ($img->url && Storage::exists($img->url)) {
                Storage::delete($img->url);
            }
        }
        $occurrence->imagens()->delete();
        $this->repository->delete($id);
    }

    public function search(?string $filter = null): LengthAwarePaginator
    {
        return $this->repository->search($filter);
    }

    /**
     * Lista ocorrências visíveis pelo motorista (atribuídas a ele ou ao órgão).
     */
    public function getOccurrencesForDriver(int $driverId, ?int $issuingsId = null, int $perPage = 15): LengthAwarePaginator
    {
        return Occurrences::forDriver($driverId, $issuingsId)
            ->with(['statusOccurence', 'typeOccurrence', 'driver'])
            ->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Motorista aceita ocorrência atribuída a ele.
     */
    public function acceptOccurrence(int $occurrenceId, int $driverId): Occurrences
    {
        $occurrence = $this->findOrFail($occurrenceId);

        if ((int) $occurrence->driver_id !== $driverId) {
            abort(403, 'Esta ocorrência não está atribuída a você.');
        }

        $statusAceita = StatusOccurrence::where('name', 'Aceita')->first();
        if (! $statusAceita) {
            abort(500, 'Status "Aceita" não configurado. Execute o seeder StatusOccurrenceDriverSeeder.');
        }

        return $this->recordStatusChange($occurrence, $statusAceita->id, 'Aceita pelo motorista.');
    }

    /**
     * Motorista recusa ocorrência atribuída a ele.
     */
    public function rejectOccurrence(int $occurrenceId, int $driverId): Occurrences
    {
        $occurrence = $this->findOrFail($occurrenceId);

        if ((int) $occurrence->driver_id !== $driverId) {
            abort(403, 'Esta ocorrência não está atribuída a você.');
        }

        $statusRecusada = StatusOccurrence::where('name', 'Recusada')->first();
        if (! $statusRecusada) {
            abort(500, 'Status "Recusada" não configurado. Execute o seeder StatusOccurrenceDriverSeeder.');
        }

        return $this->recordStatusChange($occurrence, $statusRecusada->id, 'Recusada pelo motorista.');
    }

    /**
     * Motorista atualiza status da ocorrência (Em deslocamento, Em atendimento, Finalizada).
     */
    public function updateStatusByDriver(int $occurrenceId, int $driverId, int $statusOccurrencesId): Occurrences
    {
        $occurrence = $this->findOrFail($occurrenceId);

        if ((int) $occurrence->driver_id !== $driverId) {
            abort(403, 'Esta ocorrência não está atribuída a você.');
        }

        return $this->recordStatusChange($occurrence, $statusOccurrencesId);
    }

    /**
     * Admin atualiza status da ocorrência a partir do painel do motorista (em nome do motorista).
     */
    public function updateStatusByAdmin(int $occurrenceId, int $statusOccurrencesId): Occurrences
    {
        $occurrence = $this->findOrFail($occurrenceId);

        return $this->recordStatusChange($occurrence, $statusOccurrencesId);
    }
}
