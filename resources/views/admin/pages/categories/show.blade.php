@extends('adminlte::page')

@section('title', 'Detalhes da Categoria')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => $category->name,
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Categorias', 'url' => route('categories.index')],
            ['label' => $category->name],
        ],
        'actionsHtml' => '<a href="'.route('categories.edit', $category->id).'" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Editar</a>',
    ])
@stop

@section('content')
    @include('admin.includes.alerts')

    <div class="card">
        <div class="card-body">
            <dl class="ciop-detail-grid">
                <div class="ciop-detail-item">
                    <dt>Nome</dt>
                    <dd>{{ $category->name }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>Url</dt>
                    <dd>{{ $category->url }}</dd>
                </div>
                <div class="ciop-detail-item ciop-detail-wide">
                    <dt>Descrição</dt>
                    <dd>{{ $category->description ?? '—' }}</dd>
                </div>
            </dl>
        </div>
        <div class="card-footer ciop-detail-footer">
            <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <div class="ciop-detail-footer-spacer"></div>
            <form action="{{ route('categories.destroy', $category->id) }}" method="POST" class="d-inline"
                onsubmit="return confirm('Excluir esta categoria?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="far fa-trash-alt"></i> Excluir
                </button>
            </form>
        </div>
    </div>
@endsection
