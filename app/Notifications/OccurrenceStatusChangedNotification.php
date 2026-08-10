<?php

namespace App\Notifications;

use App\Models\Occurrences;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OccurrenceStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Occurrences $occurrence,
        protected ?string $fromStatusName,
        protected string $toStatusName
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $de = $this->fromStatusName ?? 'novo';

        return [
            'occurrence_id' => $this->occurrence->id,
            'protocol' => $this->occurrence->protocol,
            'message' => "Ocorrência {$this->occurrence->protocol} mudou de \"{$de}\" para \"{$this->toStatusName}\".",
            'url' => route('driver.occurrences.show', $this->occurrence->id),
        ];
    }
}
