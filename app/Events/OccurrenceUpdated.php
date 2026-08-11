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

    public function broadcastWith(): array
    {
        return [
            'id' => $this->occurrence->id,
            'protocol' => $this->occurrence->protocol,
        ];
    }
}
