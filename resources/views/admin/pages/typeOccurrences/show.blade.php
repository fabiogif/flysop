@extends('adminlte::page')

@section('title', 'Detalhes do Tipo de Ocorrência')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => $typeOccurrence->name,
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Tipo de Ocorrência', 'url' => route('typeOccurrences.index')],
            ['label' => $typeOccurrence->name],
        ],
        'actionsHtml' => '<a href="'.route('typeOccurrences.edit', $typeOccurrence->id).'" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Editar</a>',
    ])
@stop

@section('content')
    @include('admin.includes.alerts')

    <div class="card">
        <div class="card-body">
            <dl class="ciop-detail-grid">
                <div class="ciop-detail-item">
                    <dt>Nome</dt>
                    <dd>{{ $typeOccurrence->name }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>SLA</dt>
                    <dd>{{ $typeOccurrence->sla_hours ? $typeOccurrence->sla_hours . ' horas' : '—' }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>Tipo pai</dt>
                    <dd>{{ $typeOccurrence->parent?->name ?? '—' }}</dd>
                </div>
            </dl>
        </div>
        <div class="card-footer ciop-detail-footer">
            <a href="{{ route('typeOccurrences.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <div class="ciop-detail-footer-spacer"></div>
            <form action="{{ route('typeOccurrences.destroy', $typeOccurrence->id) }}" method="POST" class="d-inline"
                onsubmit="return confirm('Excluir este tipo de ocorrência?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="far fa-trash-alt"></i> Excluir
                </button>
            </form>
        </div>
    </div>
@endsection
