<?php

namespace App\Notifications;

use App\Models\Occurrences;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OccurrenceAssignedNotification extends Notification implements ShouldQueue
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
            'message' => "Ocorrência {$this->occurrence->protocol} ({$this->occurrence->title}) foi atribuída a você.",
            'url' => route('driver.occurrences.show', $this->occurrence->id),
        ];
    }
}
