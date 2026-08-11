@extends('adminlte::page')

@section('title', 'Detalhes do Plano')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => $plan->name,
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Planos', 'url' => route('plans.index')],
            ['label' => $plan->name],
        ],
        'actionsHtml' => '<a href="'.route('plans.edit', $plan->id).'" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Editar</a>',
    ])
@stop

@section('content')
    @include('admin.includes.alerts')

    <div class="card">
        <div class="card-body">
            <dl class="ciop-detail-grid">
                <div class="ciop-detail-item">
                    <dt>Nome</dt>
                    <dd>{{ $plan->name }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>Preço</dt>
                    <dd>R$ {{ number_format($plan->price, 2, ',', '.') }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>Url</dt>
                    <dd>{{ $plan->url }}</dd>
                </div>
                <div class="ciop-detail-item ciop-detail-wide">
                    <dt>Descrição</dt>
                    <dd>{{ $plan->description ?? '—' }}</dd>
                </div>
            </dl>
        </div>
        <div class="card-footer ciop-detail-footer">
            <a href="{{ route('plans.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <div class="ciop-detail-footer-spacer"></div>
            <form action="{{ route('plans.destroy', $plan->id) }}" method="POST" class="d-inline"
                onsubmit="return confirm('Excluir este plano?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="far fa-trash-alt"></i> Excluir
                </button>
            </form>
        </div>
    </div>
@endsection
