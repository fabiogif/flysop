<?php

namespace App\Notifications;

use App\Models\Occurrences;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Aviso ao gestor do órgão de que entrou uma ocorrência para ele (feed in-app no web e no app).
 */
class NewOccurrenceForIssuingNotification extends Notification implements ShouldQueue
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
        $occurrence = $this->occurrence->loadMissing(['typeOccurrence', 'client']);
        $type = $occurrence->typeOccurrence?->name ?? 'Ocorrência';

        return [
            'occurrence_id' => $occurrence->id,
            'protocol' => $occurrence->protocol,
            'title' => $occurrence->title,
            'type' => $type,
            'address' => $occurrence->address,
            // Anônima: o gestor não vê quem abriu (o app mostra "Cidadão anônimo")
            'author' => $occurrence->is_anonymous ? null : $occurrence->client?->name,
            'message' => "Nova ocorrência {$occurrence->protocol}: {$type} — {$occurrence->title}",
            'url' => route('occurrences.show', $occurrence->id),
        ];
    }
}
