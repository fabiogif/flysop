@extends('adminlte::page')

@section('title', "Detalhes Produto - $product->title")

@section('content_header')
    <h1 class="m-0 text-dark">Detalhes Produto <b>{{ $product->title }}</b></h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @csrf
            <div class="row">
                <ul>
                    <li><img src="{{ url("storage/{$product->image}") }}" alt="{{ $product->title }}"
                            style="max-width:150px" /></li>
                    <li><b>Titulo:</b> {{ $product->title }}</li>
                    <li><b>Flag:</b> {{ $product->flag }}</li>
                    <li><b>Descrição:</b> {{ $product->description }}</li>
                    <li><b>Price:</b> {{ $product->price }}</li>
                </ul>
            </div>
            <!--row-->
            @include('admin.includes.alerts')

            <form action="{{ route('products.destroy', $product->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="far fa-trash-alt"></i>
                    <span class=m-4>Excluir</span>
                </button>
            </form>
        </div>
        <!--card-body-->
    </div>
    <!--card-->
@endsection
