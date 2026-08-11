@extends('adminlte::page')

@section('title', 'Detalhes da Permissão')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => $permission->name,
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Permissões', 'url' => route('permission.index')],
            ['label' => $permission->name],
        ],
        'actionsHtml' => '<a href="'.route('permission.edit', $permission->id).'" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Editar</a>',
    ])
@stop

@section('content')
    @include('admin.includes.alerts')

    <div class="card">
        <div class="card-body">
            <dl class="ciop-detail-grid">
                <div class="ciop-detail-item">
                    <dt>Nome</dt>
                    <dd>{{ $permission->name }}</dd>
                </div>
                <div class="ciop-detail-item ciop-detail-wide">
                    <dt>Descrição</dt>
                    <dd>{{ $permission->description ?? '—' }}</dd>
                </div>
            </dl>
        </div>
        <div class="card-footer ciop-detail-footer">
            <a href="{{ route('permission.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <div class="ciop-detail-footer-spacer"></div>
            <form action="{{ route('permission.destroy', $permission->id) }}" method="POST" class="d-inline"
                onsubmit="return confirm('Excluir esta permissão?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="far fa-trash-alt"></i> Excluir
                </button>
            </form>
        </div>
    </div>
@endsection
