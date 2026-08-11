<?php

namespace App\Events;

use App\Models\Occurrences;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispara sempre que uma ocorrência é criada ou muda de status (Fase 4). O dashboard
 * escuta este evento para trocar o polling de 60s por atualização imediata — o polling
 * continua rodando como fallback (ver resources/views/admin/pages/home/index.blade.php).
 */
class OccurrenceUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Occurrences $occurrence)
    {
    }

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('occurrences-dashboard')];
    }

    public function broadcastAs(): string
    {
        return 'OccurrenceUpdated';
    }

    /**
     * Payload completo (não só {id, protocol}) para o front poder corrigir um único
     * marcador (setIcon/setPosition) em vez de refazer o fetch inteiro a cada evento —
     * mesmo formato usado por DashboardController::occurrencesRecent(), para os dois
     * consumidores (dashboard e console de despacho) reaproveitarem a mesma função de
     * renderização de marcador. loadMissing() porque, depois de passar pela fila
     * (SerializesModels), o model chega aqui sem as relações que estavam carregadas
     * no momento do dispatch.
     */
    public function broadcastWith(): array
    {
        $this->occurrence->loadMissing(['statusOccurence', 'typeOccurrence', 'priority']);

        return [
            'id' => $this->occurrence->id,
            'protocol' => $this->occurrence->protocol,
            'title' => $this->occurrence->title,
            'name' => $this->occurrence->name,
            'address' => $this->occurrence->address,
            'latitude' => $this->occurrence->latitude ? (float) $this->occurrence->latitude : null,
            'longitude' => $this->occurrence->longitude ? (float) $this->occurrence->longitude : null,
            'status' => $this->occurrence->statusOccurence?->name ?? '—',
            'type' => $this->occurrence->typeOccurrence?->name ?? '—',
            'priority' => $this->occurrence->priority?->name,
            'priority_color' => $this->occurrence->priority?->color,
            'driver_id' => $this->occurrence->driver_id,
            'updated_at' => $this->occurrence->updated_at?->toIso8601String(),
            'updated_at_human' => $this->occurrence->updated_at?->diffForHumans(),
        ];
    }
}
