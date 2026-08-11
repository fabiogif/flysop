<?php

namespace App\Notifications;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ReportGeneratedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Report $report)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $label = Report::typeLabels()[$this->report->type] ?? $this->report->type;

        return [
            'report_id' => $this->report->id,
            'message' => "Relatório \"{$label}\" está pronto para download.",
            'url' => route('reports.download', $this->report->id),
        ];
    }
}
