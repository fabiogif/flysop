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
use App\Events\OccurrenceUpdated;
use App\Notifications\OccurrenceAssignedNotification;
use App\Notifications\OccurrenceStatusChangedNotification;
use App\Repositories\Contracts\OccurrenceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OccurrenceService
{
    /**
     * Limiares da detecção de duplicidade (Fase 6): mesmo tipo, dentro de 300m e criada
     * nas últimas 48h. Sugestão para revisão humana — nunca mescla/atribui sozinho.
     */
    private const DUPLICATE_RADIUS_KM = 0.3;
    private const DUPLICATE_WINDOW_HOURS = 48;

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

    /**
     * Criação pública (API sem login, formulário do cidadão) — corrige bug pré-existente:
     * a coluna "clients_id" é NOT NULL mas nunca era preenchida aqui, derrubando toda
     * submissão pública com erro 500. Sem multi-tenant expandido, aplica o mesmo default
     * usado em storeForAdmin() quando não há tenant: o primeiro client cadastrado.
     */
    public function createNewOccurrence(array $data): Model
    {
        if (empty($data['clients_id'])) {
            $data['clients_id'] = Client::query()->value('id') ?? 1;
        }

        if (empty($data['status_occurrences_id'])) {
            $data['status_occurrences_id'] = StatusOccurrence::where('is_terminal', false)
                ->orderBy('sort_order')
                ->value('id');
        }

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
            'statusOccurrences' => StatusOccurrence::orderBy('sort_order')->get(),
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
    public function storeForAdmin(array $data, array $anexos = [], ?string $anexoPhase = null): Occurrences
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
                $this->storeEvidence($occurrence, $file, $anexoPhase);
            }
        }

        return $occurrence;
    }

    /**
     * Evidência antes/depois (Fase 3): grava quem enviou, quando e em qual fase do
     * atendimento. Ponto único de criação de OccurrencesImagens — não duplicar este
     * bloco em outro lugar (ex.: Api\OccurrenceApiController).
     */
    private function storeEvidence(Occurrences $occurrence, UploadedFile $file, ?string $phase): void
    {
        $url = $file->store('occurrence/occurrences');

        $occurrence->imagens()->create([
            'url' => $url,
            'uploaded_by_user_id' => auth()->id(),
            'phase' => $phase,
            'captured_at' => now(),
        ]);
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

        if (! empty($data['driver_id'])) {
            $this->notifyAssignment((int) $data['driver_id'], $occurrence);
        }

        $this->detectPossibleDuplicate($occurrence);

        broadcast(new OccurrenceUpdated($occurrence))->toOthers();

        return $occurrence;
    }

    /**
     * Sugere duplicidade (Fase 6): mesmo tipo + dentro de 300m + criada nas últimas 48h.
     * Marca só a mais próxima; nunca funde/cancela automaticamente — fica para revisão
     * humana (ver Admin\OccurrencesController@dismissDuplicate).
     */
    private function detectPossibleDuplicate(Occurrences $occurrence): void
    {
        if (! $occurrence->latitude || ! $occurrence->longitude || ! $occurrence->type_occurrences_id) {
            return;
        }

        $rows = DB::select(
            <<<'SQL'
                SELECT id, protocol,
                    (6371 * acos(LEAST(1.0, GREATEST(-1.0,
                        cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?))
                        + sin(radians(?)) * sin(radians(latitude))
                    )))) AS distance_km
                FROM occurrences
                WHERE id != ?
                    AND type_occurrences_id = ?
                    AND latitude IS NOT NULL AND longitude IS NOT NULL
                    AND created_at >= ?
                ORDER BY distance_km ASC
                LIMIT 1
            SQL,
            [
                (float) $occurrence->latitude,
                (float) $occurrence->longitude,
                (float) $occurrence->latitude,
                $occurrence->id,
                $occurrence->type_occurrences_id,
                now()->subHours(self::DUPLICATE_WINDOW_HOURS),
            ]
        );

        $closest = $rows[0] ?? null;
        if ($closest && (float) $closest->distance_km <= self::DUPLICATE_RADIUS_KM) {
            $occurrence->update(['possible_duplicate_of_id' => $closest->id]);
        }
    }

    /**
     * Atendente/Supervisor confirma que não é duplicata — só limpa a sugestão, não afeta status.
     */
    public function dismissDuplicateFlag(int $occurrenceId): void
    {
        $occurrence = $this->findOrFail($occurrenceId);
        $occurrence->update(['possible_duplicate_of_id' => null]);
    }

    /**
     * Direito ao esquecimento (LGPD, Fase 6): remove os campos pessoais estruturados
     * (nome, CPF, RG, e-mail, telefone). Título/descrição/protocolo/histórico ficam —
     * são o conteúdo operacional do atendimento, não dado pessoal por si só, e removê-los
     * quebraria a rastreabilidade do serviço público prestado.
     *
     * Desliga o log de auditoria durante o update de propósito: registrar o CPF/e-mail
     * antigo no activity log (diff automático) recriaria o problema que estamos resolvendo.
     * Em vez disso grava uma entrada manual sem os valores.
     */
    public function forgetPersonalData(int $id): void
    {
        $occurrence = $this->findOrFail($id);

        $occurrence->disableLogging();
        $occurrence->update([
            'name' => '[Removido - LGPD]',
            'cpf' => null,
            'rg' => null,
            'email' => null,
            'phone' => null,
        ]);
        $occurrence->enableLogging();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($occurrence)
            ->log('Dados pessoais removidos a pedido do titular (LGPD).');
    }

    /**
     * Notifica o usuário do motorista atribuído (Fase 3 — notificações internas).
     * Silenciosa se o motorista não tiver usuário vinculado (ver Driver::user()).
     */
    private function notifyAssignment(int $driverId, Occurrences $occurrence): void
    {
        $driver = Driver::find($driverId);
        if ($driver && $driver->user) {
            $driver->user->notify(new OccurrenceAssignedNotification($occurrence));
        }
    }

    /**
     * Atualiza ocorrência pelo painel admin (anexos opcionais).
     */
    public function updateForAdmin(int $id, array $data, array $anexos = [], ?string $anexoPhase = null): void
    {
        $occurrence = $this->findOrFail($id);

        if (! empty($data['driver_id']) && (int) $occurrence->driver_id !== (int) $data['driver_id']) {
            $statusAguardando = StatusOccurrence::where('name', 'Aguardando aceitação')->first();
            if ($statusAguardando) {
                $data['status_occurrences_id'] = $statusAguardando->id;
            }
            $this->notifyAssignment((int) $data['driver_id'], $occurrence);
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
                $this->storeEvidence($occurrence, $file, $anexoPhase);
            }
        }
    }

    /**
     * Atribuição enxuta de motorista (console de despacho, Fase "Operacional" do mapa) —
     * mesma regra de negócio de updateForAdmin() quando driver_id muda (status vai para
     * "Aguardando aceitação" + notifica o motorista), mas sem exigir o restante do
     * formulário completo. Ponto único para esta ação vinda do mapa/console.
     */
    public function assignDriver(int $occurrenceId, int $driverId): Occurrences
    {
        $occurrence = $this->findOrFail($occurrenceId);

        if ((int) $occurrence->driver_id === $driverId) {
            return $occurrence;
        }

        $occurrence->update(['driver_id' => $driverId]);

        $statusAguardando = StatusOccurrence::where('name', 'Aguardando aceitação')->first();
        if ($statusAguardando) {
            $occurrence = $this->recordStatusChange($occurrence, $statusAguardando->id);
        }

        $this->notifyAssignment($driverId, $occurrence);

        broadcast(new OccurrenceUpdated($occurrence))->toOthers();

        return $occurrence;
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
     * Ponto único de mudança de status: valida a transição, atualiza a ocorrência e grava
     * o histórico (occurrence_status_history). Nenhuma outra rotina deve escrever em
     * status_occurrences_id diretamente.
     */
    private function recordStatusChange(Occurrences $occurrence, int $newStatusId, ?string $note = null): Occurrences
    {
        $fromStatusId = $occurrence->status_occurrences_id;

        if ((int) $fromStatusId === $newStatusId) {
            return $occurrence;
        }

        $this->guardStatusTransition($occurrence, $fromStatusId, $newStatusId);

        $occurrence->update(['status_occurrences_id' => $newStatusId]);

        OccurrenceStatusHistory::create([
            'occurrence_id' => $occurrence->id,
            'from_status_id' => $fromStatusId,
            'to_status_id' => $newStatusId,
            'changed_by_user_id' => auth()->id(),
            'note' => $note,
        ]);

        $this->notifyStatusChange($occurrence, $fromStatusId, $newStatusId);
        $this->syncDriverStatus($occurrence, $newStatusId);
        broadcast(new OccurrenceUpdated($occurrence))->toOthers();

        return $occurrence->fresh();
    }

    /**
     * Notifica o motorista atribuído sobre a mudança de status (Fase 3). Silenciosa se
     * não houver motorista atribuído ou se ele não tiver usuário vinculado.
     */
    private function notifyStatusChange(Occurrences $occurrence, ?int $fromStatusId, int $newStatusId): void
    {
        if (! $occurrence->driver_id || ! $occurrence->driver || ! $occurrence->driver->user) {
            return;
        }

        // Não notificar o próprio motorista quando a mudança foi feita por ele mesmo.
        if (auth()->id() === $occurrence->driver->user->id) {
            return;
        }

        $fromName = $fromStatusId ? StatusOccurrence::find($fromStatusId)?->name : null;
        $toName = StatusOccurrence::find($newStatusId)?->name ?? '—';

        $occurrence->driver->user->notify(new OccurrenceStatusChangedNotification($occurrence, $fromName, $toName));
    }

    /**
     * Reflete o status da ocorrência no status do motorista atribuído (Fase 5 — side effect
     * explícito no Service, nunca em Model, ver docs/specs/modules.md). Silencioso se não
     * houver motorista atribuído.
     */
    private function syncDriverStatus(Occurrences $occurrence, int $newStatusId): void
    {
        $driver = $occurrence->driver;
        if (! $driver) {
            return;
        }

        $statusName = StatusOccurrence::where('id', $newStatusId)->value('name');

        $driverStatus = match ($statusName) {
            'Em deslocamento' => Driver::STATUS_EM_DESLOCAMENTO,
            'Equipe no local', 'Em atendimento' => Driver::STATUS_EM_ATENDIMENTO,
            'Finalizada', 'Cancelada', 'Duplicada', 'Recusada' => Driver::STATUS_DISPONIVEL,
            default => null,
        };

        if ($driverStatus !== null && $driverStatus !== $driver->status) {
            $driver->update(['status' => $driverStatus]);
        }
    }

    /**
     * Uma ocorrência num status terminal (Finalizada/Cancelada/Duplicada) só pode mudar de
     * status através de "Reaberta" — e só quem tem a permissão reopen (ver OccurrencePolicy).
     * Qualquer outra transição a partir de um status terminal é bloqueada.
     */
    private function guardStatusTransition(Occurrences $occurrence, ?int $fromStatusId, int $newStatusId): void
    {
        if (! $fromStatusId) {
            return;
        }

        $fromIsTerminal = (bool) StatusOccurrence::where('id', $fromStatusId)->value('is_terminal');
        if (! $fromIsTerminal) {
            return;
        }

        $reabertaId = StatusOccurrence::where('name', 'Reaberta')->value('id');
        if ($newStatusId !== $reabertaId) {
            abort(422, 'Esta ocorrência está num status final e só pode ser movida para "Reaberta".');
        }

        if (! auth()->check() || ! auth()->user()->can('reopen', $occurrence)) {
            abort(403, 'Você não tem permissão para reabrir esta ocorrência.');
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
