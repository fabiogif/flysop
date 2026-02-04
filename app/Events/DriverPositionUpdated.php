<?php

namespace App\Events;

use App\Models\DriverPosition;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverPositionUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public DriverPosition $position
    ) {
    }

    /**
     * Get the channels the event should broadcast on.
     * Apenas quando a posição está vinculada a uma ocorrência (motorista em deslocamento).
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        if ($this->position->occurrence_id === null) {
            return [];
        }

        return [
            new PrivateChannel('occurrence.' . $this->position->occurrence_id),
        ];
    }

    /**
     * Nome do evento no canal (frontend escuta por este nome).
     */
    public function broadcastAs(): string
    {
        return 'DriverPositionUpdated';
    }

    /**
     * Payload enviado ao frontend.
     */
    public function broadcastWith(): array
    {
        $driver = $this->position->driver;

        return [
            'driver_id' => $this->position->driver_id,
            'occurrence_id' => $this->position->occurrence_id,
            'latitude' => (float) $this->position->latitude,
            'longitude' => (float) $this->position->longitude,
            'created_at' => $this->position->created_at?->toIso8601String(),
            'driver' => $driver ? [
                'id' => $driver->id,
                'name' => $driver->name,
            ] : null,
        ];
    }
}
