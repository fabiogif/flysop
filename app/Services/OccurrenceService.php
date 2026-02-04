<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Driver;
use App\Models\Issuing;
use App\Models\Occurrences;
use App\Models\StatusOccurrence;
use App\Models\TypeOccurrence;
use App\Repositories\Contracts\OccurrenceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
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
        return $this->repository->createNewOccurrence($data);
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
        $occurrence = $this->repository->createNewOccurrence($data);

        foreach ($anexos as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $url = $file->store('occurrence/occurrences');
                $occurrence->imagens()->create(['url' => $url]);
            }
        }

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

        $occurrence->update($data);

        foreach ($anexos as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $url = $file->store('occurrence/occurrences');
                $occurrence->imagens()->create(['url' => $url]);
            }
        }
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

        $occurrence->update(['status_occurrences_id' => $statusAceita->id]);

        return $occurrence->fresh();
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

        $occurrence->update(['status_occurrences_id' => $statusRecusada->id]);

        return $occurrence->fresh();
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

        $occurrence->update(['status_occurrences_id' => $statusOccurrencesId]);

        return $occurrence->fresh();
    }
}
