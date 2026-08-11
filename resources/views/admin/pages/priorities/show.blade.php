@extends('adminlte::page')

@section('title', 'Detalhes da Prioridade')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => $priority->name,
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Prioridades', 'url' => route('priorities.index')],
            ['label' => $priority->name],
        ],
        'actionsHtml' => '<a href="'.route('priorities.edit', $priority->id).'" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Editar</a>',
    ])
@stop

@section('content')
    @include('admin.includes.alerts')

    <div class="card">
        <div class="card-body">
            <dl class="ciop-detail-grid">
                <div class="ciop-detail-item">
                    <dt>Nome</dt>
                    <dd>{{ $priority->name }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>Peso</dt>
                    <dd>{{ $priority->weight }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>Cor</dt>
                    <dd>
                        @if ($priority->color)
                            <span class="ciop-color-swatch" style="background-color: {{ $priority->color }};"></span>
                            {{ $priority->color }}
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>SLA padrão</dt>
                    <dd>{{ $priority->default_sla_hours ? $priority->default_sla_hours . ' horas' : '—' }}</dd>
                </div>
            </dl>
        </div>
        <div class="card-footer ciop-detail-footer">
            <a href="{{ route('priorities.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <div class="ciop-detail-footer-spacer"></div>
            <form action="{{ route('priorities.destroy', $priority->id) }}" method="POST" class="d-inline"
                onsubmit="return confirm('Excluir esta prioridade?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="far fa-trash-alt"></i> Excluir
                </button>
            </form>
        </div>
    </div>
@endsection
