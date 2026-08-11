@extends('adminlte::page')

@section('title', 'Detalhes do Cargo')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => $role->name,
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Cargos', 'url' => route('roles.index')],
            ['label' => $role->name],
        ],
        'actionsHtml' => '<a href="'.route('roles.edit', $role->id).'" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Editar</a>',
    ])
@stop

@section('content')
    @include('admin.includes.alerts')

    <div class="card">
        <div class="card-body">
            <dl class="ciop-detail-grid">
                <div class="ciop-detail-item">
                    <dt>Nome</dt>
                    <dd>{{ $role->name }}</dd>
                </div>
                <div class="ciop-detail-item ciop-detail-wide">
                    <dt>Descrição</dt>
                    <dd>{{ $role->description ?? '—' }}</dd>
                </div>
            </dl>
        </div>
        <div class="card-footer ciop-detail-footer">
            <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <div class="ciop-detail-footer-spacer"></div>
            <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="d-inline"
                onsubmit="return confirm('Excluir este cargo?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="far fa-trash-alt"></i> Excluir
                </button>
            </form>
        </div>
    </div>
@endsection
