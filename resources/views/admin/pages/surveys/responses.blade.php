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
        'actionsHtml' => '<a href="'.route('surveys.show', $survey->id).'" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Voltar</a>',
    ])
@stop

@section('content')
    @include('admin.includes.alerts')

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
