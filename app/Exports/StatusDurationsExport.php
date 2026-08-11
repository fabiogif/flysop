<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Tempo médio (em horas) que as ocorrências passam em cada status, calculado a partir de
 * occurrence_status_history (Fase 1/3). Usa LEAD() (window function do Postgres) para achar
 * quando cada status foi "abandonado"; transições ainda em aberto (sem próximo registro) são
 * ignoradas — não faz sentido contar tempo de um status que a ocorrência ainda está.
 */
class StatusDurationsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection()
    {
        $rows = DB::select(<<<'SQL'
            SELECT
                so.name AS status_name,
                COUNT(*) AS transitions,
                AVG(EXTRACT(EPOCH FROM (sub.next_created_at - sub.created_at)) / 3600.0) AS avg_hours
            FROM (
                SELECT
                    occurrence_id,
                    to_status_id,
                    created_at,
                    LEAD(created_at) OVER (PARTITION BY occurrence_id ORDER BY created_at) AS next_created_at
                FROM occurrence_status_history
            ) sub
            JOIN status_occurrences so ON so.id = sub.to_status_id
            WHERE sub.next_created_at IS NOT NULL
            GROUP BY so.name, so.sort_order
            ORDER BY so.sort_order
        SQL);

        return collect($rows);
    }

    public function headings(): array
    {
        return ['Status', 'Transições consideradas', 'Tempo médio (horas)'];
    }

    public function map($row): array
    {
        return [
            $row->status_name,
            $row->transitions,
            round((float) $row->avg_hours, 2),
        ];
    }
}
