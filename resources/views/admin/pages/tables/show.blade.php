@extends('adminlte::page')

@section('title', "Detalhes Mesa { $table->identify }")

@section('content_header')
    <h1 class="m-0 text-dark">Detalhes Mesa <b>{{ $table->identify }}</b></h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @csrf
            <div class="row">
                <ul>
                    <li><b>Indentificador:</b> {{ $table->identify }}</li>
                    <li><b>Descrição:</b> {{ $table->description }}</li>
                </ul>
            </div>
            <!--row-->
            @include('admin.includes.alerts')

            <form action="{{ route('tables.destroy', $table->id) }}" method="POST">
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
