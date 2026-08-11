@extends('adminlte::page')
@section('title', 'Produtos')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => 'Produtos',
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Produtos'],
        ],
        'actionsHtml' => '<a href="'.route('products.create').'" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Adicionar</a>',
    ])
@stop

@section('content')
    <div class="card">
        @include('admin.includes.alerts')

        @include('admin.includes.search-toolbar', [
            'action' => route('products.search'),
            'placeholder' => 'Titulo ou Descrição',
            'filters' => $filters ?? [],
        ])

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover ciop-table mb-0">
                    <thead>
                        <tr>
                            <th>Imagem</th>
                            <th>Titulo</th>
                            <th>Preço</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr>
                                <td>
                                    <img src="{{ url("storage/{$product->image}") }}" alt="{{ $product->title }}"
                                        style="max-width:150px" />
                                </td>
                                <td>{{ $product->title }}</td>
                                <td>{{ $product->price }}</td>
                                <td>
                                    <div class="ciop-actions">
                                        <a href="{{ route('products.show', $product->id) }}" class="btn btn-outline-info btn-sm" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('products.edit', $product->id) }}" class="btn btn-outline-warning btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="ciop-empty">Nenhum registro encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            @if (isset($filters))
                {!! $products->appends($filters)->links() !!}
            @else
                {!! $products->links() !!}
            @endif
        </div>
    </div>
@endsection
