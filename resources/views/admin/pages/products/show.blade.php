@extends('adminlte::page')

@section('title', 'Detalhes do Produto')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => $product->title,
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Produtos', 'url' => route('products.index')],
            ['label' => $product->title],
        ],
        'actionsHtml' => '<a href="'.route('products.edit', $product->id).'" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Editar</a>',
    ])
@stop

@section('content')
    @include('admin.includes.alerts')

    <div class="card">
        <div class="card-body">
            <dl class="ciop-detail-grid">
                <div class="ciop-detail-item">
                    <dt>Imagem</dt>
                    <dd>
                        <img src="{{ url("storage/{$product->image}") }}" alt="{{ $product->title }}"
                            style="max-width:150px" />
                    </dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>Titulo</dt>
                    <dd>{{ $product->title }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>Flag</dt>
                    <dd>{{ $product->flag ?? '—' }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>Preço</dt>
                    <dd>{{ $product->price }}</dd>
                </div>
                <div class="ciop-detail-item ciop-detail-wide">
                    <dt>Descrição</dt>
                    <dd>{{ $product->description ?? '—' }}</dd>
                </div>
            </dl>
        </div>
        <div class="card-footer ciop-detail-footer">
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <div class="ciop-detail-footer-spacer"></div>
            <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline"
                onsubmit="return confirm('Excluir este produto?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="far fa-trash-alt"></i> Excluir
                </button>
            </form>
        </div>
    </div>
@endsection
