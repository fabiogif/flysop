<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #2b2b2b; }
        h1 { font-size: 18px; color: #1f3b6b; margin-bottom: 0; }
        .muted { color: #5c6773; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.fields td { padding: 4px 6px; border-bottom: 1px solid #d5dee8; vertical-align: top; }
        table.fields td.label { width: 160px; font-weight: bold; color: #1f3b6b; }
        table.history th, table.history td { border: 1px solid #d5dee8; padding: 4px 6px; font-size: 10px; text-align: left; }
        table.history th { background: #1f3b6b; color: #fff; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; color: #fff; font-size: 10px; }
    </style>
</head>
<body>
    <h1>Ficha de Ocorrência</h1>
    <p class="muted">Protocolo {{ $occurrence->protocol ?? '—' }} — gerado em {{ now()->format('d/m/Y H:i') }}</p>

    <table class="fields">
        <tr><td class="label">Título</td><td>{{ $occurrence->title }}</td></tr>
        <tr><td class="label">Solicitante</td><td>{{ $occurrence->name }}</td></tr>
        <tr><td class="label">Status</td><td>{{ $occurrence->statusOccurence->name ?? '—' }}</td></tr>
        <tr><td class="label">Tipo</td><td>{{ $occurrence->typeOccurrence->name ?? '—' }}</td></tr>
        <tr><td class="label">Prioridade</td><td>{{ $occurrence->priority->name ?? '—' }}</td></tr>
        <tr><td class="label">Motorista</td><td>{{ $occurrence->driver->name ?? '—' }}</td></tr>
        <tr><td class="label">Endereço</td><td>{{ $occurrence->address }}</td></tr>
        <tr><td class="label">Bairro/Cidade/UF</td><td>{{ implode(' - ', array_filter([$occurrence->neighborhood, $occurrence->city, $occurrence->state])) ?: '—' }}</td></tr>
        <tr><td class="label">Criado em</td><td>{{ $occurrence->created_at?->format('d/m/Y H:i') }}</td></tr>
        <tr><td class="label">Prazo (SLA)</td><td>{{ $occurrence->due_at?->format('d/m/Y H:i') ?? '—' }}</td></tr>
        <tr><td class="label">Descrição</td><td>{{ $occurrence->description }}</td></tr>
    </table>

    <h3>Histórico de status</h3>
    <table class="history">
        <thead>
            <tr><th>De</th><th>Para</th><th>Alterado por</th><th>Data</th><th>Observação</th></tr>
        </thead>
        <tbody>
            @forelse ($occurrence->statusHistory as $entry)
                <tr>
                    <td>{{ $entry->fromStatus->name ?? '—' }}</td>
                    <td>{{ $entry->toStatus->name ?? '—' }}</td>
                    <td>{{ $entry->changedBy->name ?? 'Sistema' }}</td>
                    <td>{{ $entry->created_at?->format('d/m/Y H:i') }}</td>
                    <td>{{ $entry->note ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Sem histórico registrado.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
