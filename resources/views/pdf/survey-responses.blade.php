<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #2b2b2b; }
        h1 { font-size: 18px; color: #1f3b6b; margin-bottom: 0; }
        h3 { color: #1f3b6b; margin-top: 18px; }
        .muted { color: #5c6773; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.grid th, table.grid td { border: 1px solid #d5dee8; padding: 4px 6px; font-size: 10px; text-align: left; vertical-align: top; }
        table.grid th { background: #1f3b6b; color: #fff; }
        table.grid td.label { width: 45%; font-weight: bold; color: #1f3b6b; }
        .response { page-break-inside: avoid; margin-top: 12px; }
    </style>
</head>
<body>
    <h1>{{ $summary === null ? 'Resposta' : 'Respostas' }} da pesquisa: {{ $survey->title }}</h1>
    <p class="muted">
        {{ $responses->count() }} resposta(s)
        @if (!empty($period['date_from']) || !empty($period['date_to']))
            — período {{ !empty($period['date_from']) ? \Carbon\Carbon::parse($period['date_from'])->format('d/m/Y') : 'início' }}
            a {{ !empty($period['date_to']) ? \Carbon\Carbon::parse($period['date_to'])->format('d/m/Y') : 'hoje' }}
        @endif
        — gerado em {{ now()->format('d/m/Y H:i') }}
    </p>

    @if ($summary !== null)
    <h3>Resumo por pergunta</h3>
    @foreach ($summary as $item)
        <table class="grid">
            <thead>
                <tr>
                    <th colspan="2">
                        {{ $loop->iteration }}. {{ $item['question']->prompt }}
                        ({{ $item['total'] }} resposta(s)@if ($item['average'] !== null), média {{ number_format($item['average'], 2, ',', '') }}@endif)
                    </th>
                </tr>
            </thead>
            @if ($item['counts'])
                <tbody>
                    @foreach ($item['counts'] as $label => $count)
                        <tr>
                            <td class="label">{{ $label }}</td>
                            <td>{{ $count }} ({{ $item['total'] ? round($count * 100 / $item['total']) : 0 }}%)</td>
                        </tr>
                    @endforeach
                </tbody>
            @endif
        </table>
    @endforeach

    <h3>Respostas individuais</h3>
    @endif
    @forelse ($responses as $response)
        @php $answers = $response->answers->keyBy('survey_question_id'); @endphp
        <div class="response">
            <strong>Resposta #{{ $response->id }} — {{ $response->respondent_name ?: 'Anônimo' }}</strong>
            <span class="muted">— {{ $response->submitted_at?->format('d/m/Y H:i') ?? '—' }}</span>
            <table class="grid">
                @foreach ($survey->questions as $question)
                    <tr>
                        <td class="label">{{ $question->prompt }}</td>
                        @php $answer = $answers->get($question->id); @endphp
                        <td>
                            {{ $answer->value ?? '—' }}
                            @if ($answer && $answer->justification)
                                <br><span class="muted">Justificativa: {{ $answer->justification }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    @empty
        <p class="muted">Nenhuma resposta recebida ainda.</p>
    @endforelse
</body>
</html>
