<?php

namespace App\Notifications;

use App\Models\Occurrences;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Aviso ao cidadão autor de que a ocorrência foi concluída ou indeferida (feed do app,
 * GET /api/notifications). Sem dados de servidor — só protocolo e novo status.
 */
class OccurrenceStatusForCitizenNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Occurrences $occurrence,
        protected string $statusName
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $message = $this->statusName === 'Indeferida'
            ? "Sua ocorrência {$this->occurrence->protocol} foi indeferida."
            : "Sua ocorrência {$this->occurrence->protocol} foi concluída. Avalie o atendimento no app.";

        return [
            'occurrence_id' => $this->occurrence->id,
            'protocol' => $this->occurrence->protocol,
            'status' => $this->statusName,
            'title' => "Ocorrência {$this->statusName}",
            'message' => $message,
        ];
    }
}
