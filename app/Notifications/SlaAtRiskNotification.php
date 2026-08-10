<?php

namespace App\Notifications;

use App\Models\Occurrences;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SlaAtRiskNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Occurrences $occurrence)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'occurrence_id' => $this->occurrence->id,
            'protocol' => $this->occurrence->protocol,
            'message' => "Ocorrência {$this->occurrence->protocol} está com o prazo (SLA) próximo do vencimento: {$this->occurrence->due_at->format('d/m/Y H:i')}.",
            'url' => route('occurrences.show', $this->occurrence->id),
        ];
    }
}
