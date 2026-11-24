@extends('adminlte::page')

@section('title', 'Respostas da Pesquisa')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => 'Respostas',
        'subtitle' => $survey->title,
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Pesquisas', 'url' => route('surveys.index')],
            ['label' => $survey->title, 'url' => route('surveys.show', $survey->id)],
            ['label' => 'Respostas'],
        ],
        'actionsHtml' => '<a href="'.route('surveys.responses.pdf', $survey->id).'" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm mr-1"><i class="fas fa-print"></i> Imprimir respostas</a>'
            .'<a href="'.route('surveys.show', $survey->id).'" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Voltar</a>',
    ])
@stop

@section('content')
    @include('admin.includes.alerts')

    <div class="card mb-3">
        <div class="card-header">Resumo por pergunta · {{ $responses->total() }} resposta(s)</div>
        <div class="card-body">
            @foreach ($summary as $item)
                <div class="mb-3">
                    <strong>{{ $loop->iteration }}. {{ $item['question']->prompt }}</strong>
                    <small class="text-muted">
                        · {{ $item['total'] }} resposta(s)
                        @if ($item['average'] !== null) · média {{ number_format($item['average'], 2, ',', '') }} @endif
                    </small>
                    @foreach ($item['counts'] as $label => $count)
                        @php $percent = $item['total'] ? round($count * 100 / $item['total']) : 0; @endphp
                        <div class="d-flex align-items-center mt-1">
                            <span class="text-truncate" style="width: 30%;">{{ $label }}</span>
                            <div class="progress flex-grow-1 mx-2" style="height: 14px;">
                                <div class="progress-bar" role="progressbar" style="width: {{ $percent }}%;"
                                    aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small style="width: 70px;" class="text-right">{{ $count }} ({{ $percent }}%)</small>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>

    @forelse ($responses as $response)
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between">
                <span>Resposta #{{ $response->id }}</span>
                <small class="text-muted">
                    {{ $response->submitted_at ? $response->submitted_at->format('d/m/Y H:i') : '—' }}
                    @if ($response->ip)
                        · {{ $response->ip }}
                    @endif
                </small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover ciop-table mb-0">
                        <thead>
                            <tr>
                                <th>Pergunta</th>
                                <th>Resposta</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($survey->questions as $question)
                                @php
                                    $answer = $response->answers->firstWhere('survey_question_id', $question->id);
                                @endphp
                                <tr>
                                    <td>{{ $question->prompt }}</td>
                                    <td>{{ $answer->value ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body ciop-empty">Nenhuma resposta recebida ainda.</div>
        </div>
    @endforelse

    <div class="mt-2">
        {!! $responses->links() !!}
    </div>
@endsection
