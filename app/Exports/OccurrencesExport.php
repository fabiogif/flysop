<?php

namespace App\Exports;

use App\Models\Occurrences;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Listagem de ocorrências (Fase 6). Mesmos filtros do dashboard
 * (App\Http\Controllers\Admin\DashboardController::occurrencesRecent) — não duplicar a
 * lógica de filtro, só a query muda (sem limit, para exportação completa).
 */
class OccurrencesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(protected array $filters = [])
    {
    }

    public function collection()
    {
        return Occurrences::with(['statusOccurence', 'typeOccurrence', 'priority', 'driver'])
            ->when($this->filters['status_occurrences_id'] ?? null, fn ($q, $v) => $q->where('status_occurrences_id', $v))
            ->when($this->filters['type_occurrences_id'] ?? null, fn ($q, $v) => $q->where('type_occurrences_id', $v))
            ->when($this->filters['priority_id'] ?? null, fn ($q, $v) => $q->where('priority_id', $v))
            ->when($this->filters['driver_id'] ?? null, fn ($q, $v) => $q->where('driver_id', $v))
            ->when($this->filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($this->filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->orderByDesc('created_at')
            ->get();
    }

    public function headings(): array
    {
        return ['Protocolo', 'Título', 'Nome', 'Status', 'Tipo', 'Prioridade', 'Motorista', 'Endereço', 'Criado em', 'Prazo (SLA)'];
    }

    public function map($occurrence): array
    {
        return [
            $occurrence->protocol,
            $occurrence->title,
            $occurrence->name,
            $occurrence->statusOccurence->name ?? '—',
            $occurrence->typeOccurrence->name ?? '—',
            $occurrence->priority->name ?? '—',
            $occurrence->driver->name ?? '—',
            $occurrence->address,
            $occurrence->created_at?->format('d/m/Y H:i'),
            $occurrence->due_at?->format('d/m/Y H:i'),
        ];
    }
}
