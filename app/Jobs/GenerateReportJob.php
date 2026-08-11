<?php

namespace App\Jobs;

use App\Exports\OccurrencesExport;
use App\Exports\StatusDurationsExport;
use App\Models\Report;
use App\Notifications\ReportGeneratedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Geração de relatório fora da request (Fase 6 — worker da Fase 0). Fica pendurado no
 * disco privado (não S3 público) e some após ser baixado/expirar (ver LGPD/retenção).
 */
class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(protected int $reportId)
    {
    }

    public function handle(): void
    {
        $report = Report::find($this->reportId);
        if (! $report) {
            return;
        }

        try {
            $filename = 'reports/' . $report->type . '-' . $report->id . '-' . now()->format('YmdHis') . '.xlsx';

            $export = match ($report->type) {
                Report::TYPE_OCCURRENCES => new OccurrencesExport($report->filters ?? []),
                Report::TYPE_STATUS_DURATIONS => new StatusDurationsExport(),
                default => null,
            };

            if (! $export) {
                throw new \InvalidArgumentException("Tipo de relatório desconhecido: {$report->type}");
            }

            Excel::store($export, $filename, 'local');

            $report->update([
                'status' => Report::STATUS_READY,
                'file_path' => $filename,
                'ready_at' => now(),
            ]);

            $report->user?->notify(new ReportGeneratedNotification($report));
        } catch (\Throwable $e) {
            $report->update([
                'status' => Report::STATUS_FAILED,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
