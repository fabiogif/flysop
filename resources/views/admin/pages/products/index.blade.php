@extends('adminlte::page')
@section('title', 'Produtos')

@section('content_header')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Painel de Controle</a> </li>
        <li class="breadcrumb-item active"><a href="{{ route('products.index') }}">Produtos</a> </li>
    </ol>

    <h1 class="m-0 text-dark">Produtos
        <a href="{{ route('products.create') }}" class="btn btn-primary mr-5">
            <i class="fas fa-save"></i>
            <span class=m-4>Adicionar</span>
        </a>
    </h1>
@stop

@section('content')
    <div class="card">
        @include('admin.includes.alerts')

        <div class="card-header">
            <form action="{{ route('products.search') }}" method="POST" class="form form-inline">
                @csrf
                <div class="form-group">
                    <input type="text" class="form-control mr-2" name="filter" placeholder="Titulo ou Descrição"
                        value="{{ $filters['filter'] ?? '' }}">
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-search"></i>
                        <span class=m-4>Pesquisar</span>
                    </button>
                </div>
            </form>
        </div>


        <div class="card-body">
            <table class="table table-condensed">
                <thead>
                    <tr>
                        <th style="max-width:150px">Imagem</th>
                        <th>Titulo</th>
                        <th>Preço</th>
                        <th width="150px">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr>
                            <td><img src="{{ url("storage/{$product->image}") }}" alt="{{ $product->title }}"
                                    style="max-width:150px" /></td>
                            <td>{{ $product->title }}</td>
                            <td>{{ $product->price }}</td>
                            <td style=" width: 10px">
                                <a href="{{ route('products.edit', $product->id) }}" class="btn btn-warning">Alterar</a>
                                <a href="{{ route('products.show', $product->id) }}" class="btn btn-info">Visualizar</a>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            @if (isset($filters))
                {!! $products->appends($filters)->links() !!}
            @else
                {!! $products->links() !!}
            @endif
        </div>
    </div>
@stop
