@extends('adminlte::page')

@section('title', 'Detalhes do Detalhe')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => $details->name,
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Planos', 'url' => route('plans.index')],
            ['label' => $plan->name, 'url' => route('plans.show', $plan->url)],
            ['label' => 'Detalhes', 'url' => route('details.plans.index', $plan->url)],
            ['label' => $details->name],
        ],
        'actionsHtml' => '<a href="'.route('details.plans.edit', [$plan->id, $details->id]).'" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Editar</a>',
    ])
@stop

@section('content')
    @include('admin.includes.alerts')

    <div class="card">
        <div class="card-body">
            <dl class="ciop-detail-grid">
                <div class="ciop-detail-item">
                    <dt>Nome</dt>
                    <dd>{{ $details->name }}</dd>
                </div>
            </dl>
        </div>
        <div class="card-footer ciop-detail-footer">
            <a href="{{ route('details.plans.index', $plan->url) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <div class="ciop-detail-footer-spacer"></div>
            <form action="{{ route('details.plans.delete', [$plan->id, $details->id]) }}" method="POST" class="d-inline"
                onsubmit="return confirm('Excluir este detalhe?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="far fa-trash-alt"></i> Excluir
                </button>
            </form>
        </div>
    </div>
@endsection
