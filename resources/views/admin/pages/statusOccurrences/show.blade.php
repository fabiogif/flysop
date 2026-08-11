@extends('adminlte::page')

@section('title', 'Detalhes do Status de Ocorrência')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => $statusOccurrence->name,
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Status de Ocorrência', 'url' => route('statusOccurrences.index')],
            ['label' => $statusOccurrence->name],
        ],
        'actionsHtml' => '<a href="'.route('statusOccurrences.edit', $statusOccurrence->id).'" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Editar</a>',
    ])
@stop

@section('content')
    @include('admin.includes.alerts')

    <div class="card">
        <div class="card-body">
            <dl class="ciop-detail-grid">
                <div class="ciop-detail-item">
                    <dt>Nome</dt>
                    <dd>{{ $statusOccurrence->name }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>Ordem</dt>
                    <dd>{{ $statusOccurrence->sort_order }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>Terminal</dt>
                    <dd>{{ $statusOccurrence->is_terminal ? 'Sim' : 'Não' }}</dd>
                </div>
            </dl>
        </div>
        <div class="card-footer ciop-detail-footer">
            <a href="{{ route('statusOccurrences.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <div class="ciop-detail-footer-spacer"></div>
            <form action="{{ route('statusOccurrences.destroy', $statusOccurrence->id) }}" method="POST" class="d-inline"
                onsubmit="return confirm('Excluir este status?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="far fa-trash-alt"></i> Excluir
                </button>
            </form>
        </div>
    </div>
@endsection
