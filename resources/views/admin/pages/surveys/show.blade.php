@extends('adminlte::page')

@section('title', 'Detalhes da Pesquisa')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => $survey->title,
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Pesquisas', 'url' => route('surveys.index')],
            ['label' => $survey->title],
        ],
        'actionsHtml' => '<a href="'.route('surveys.edit', $survey->id).'" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Editar</a>',
    ])
@stop

@section('content')
    @include('admin.includes.alerts')

    <div class="card">
        <div class="card-body">
            <dl class="ciop-detail-grid">
                <div class="ciop-detail-item">
                    <dt>Status</dt>
                    <dd>
                        @if ($survey->is_active)
                            <span class="badge badge-success">Ativa</span>
                        @else
                            <span class="badge badge-secondary">Inativa</span>
                        @endif
                    </dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>Perguntas</dt>
                    <dd>{{ $survey->questions->count() }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>Respostas</dt>
                    <dd>{{ $survey->responses_count }}</dd>
                </div>
                <div class="ciop-detail-item ciop-detail-wide">
                    <dt>Descrição</dt>
                    <dd>{{ $survey->description ?: '—' }}</dd>
                </div>
                <div class="ciop-detail-item ciop-detail-wide">
                    <dt>Link público</dt>
                    <dd>
                        <div class="input-group">
                            <input type="text" class="form-control" id="survey-public-url" readonly
                                value="{{ $survey->publicUrl() }}">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" id="survey-copy-link">
                                    <i class="fas fa-copy"></i> Copiar
                                </button>
                                <a href="{{ $survey->publicUrl() }}" target="_blank" rel="noopener" class="btn btn-outline-info">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>
                        </div>
                    </dd>
                </div>
            </dl>

            <h5 class="mt-4 mb-3">Perguntas</h5>
            <div class="table-responsive">
                <table class="table table-hover ciop-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Enunciado</th>
                            <th>Tipo</th>
                            <th>Obrigatória</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($survey->questions as $question)
                            <tr>
                                <td>{{ $question->sort_order + 1 }}</td>
                                <td>{{ $question->prompt }}</td>
                                <td>{{ \App\Models\SurveyQuestion::typeLabels()[$question->type] ?? $question->type }}</td>
                                <td>{{ $question->required ? 'Sim' : 'Não' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="ciop-empty">Sem perguntas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer ciop-detail-footer">
            <a href="{{ route('surveys.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <a href="{{ route('surveys.responses', $survey->id) }}" class="btn btn-outline-info btn-sm">
                <i class="fas fa-inbox"></i> Ver respostas
            </a>
            <form action="{{ route('surveys.toggle', $survey->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm">
                    {{ $survey->is_active ? 'Desativar' : 'Ativar' }}
                </button>
            </form>
            <div class="ciop-detail-footer-spacer"></div>
            <form action="{{ route('surveys.destroy', $survey->id) }}" method="POST" class="d-inline"
                onsubmit="return confirm('Excluir esta pesquisa e todas as respostas?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="far fa-trash-alt"></i> Excluir
                </button>
            </form>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Respostas recentes</span>
            <a href="{{ route('surveys.responses', $survey->id) }}" class="btn btn-sm btn-outline-secondary">Ver todas</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover ciop-table mb-0">
                    <thead>
                        <tr>
                            <th>Enviada em</th>
                            <th>IP</th>
                            <th>Respostas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentResponses as $response)
                            <tr>
                                <td>{{ $response->submitted_at ? $response->submitted_at->format('d/m/Y H:i') : '—' }}</td>
                                <td>{{ $response->ip ?? '—' }}</td>
                                <td>{{ $response->answers_count }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="ciop-empty">Nenhuma resposta ainda.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    (function () {
        var btn = document.getElementById('survey-copy-link');
        var input = document.getElementById('survey-public-url');
        if (!btn || !input) return;
        btn.addEventListener('click', function () {
            input.select();
            input.setSelectionRange(0, 99999);
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(input.value);
            } else {
                document.execCommand('copy');
            }
            btn.innerHTML = '<i class="fas fa-check"></i> Copiado';
            setTimeout(function () { btn.innerHTML = '<i class="fas fa-copy"></i> Copiar'; }, 1500);
        });
    })();
    </script>
@endsection
