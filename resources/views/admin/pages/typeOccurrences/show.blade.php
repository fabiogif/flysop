@extends('adminlte::page')

@section('title', "Detalhes Tipo de Ocorrência { $typeOccurrence->name }")

@section('content_header')
    <h1 class="m-0 text-dark">Detalhes Tipo de Ocorrência <b>{{ $typeOccurrence->name }}</b></h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @csrf
            <div class="row">
                <ul>
                    <li><b>Nome:</b> {{ $typeOccurrence->name }}</li>
                </ul>
            </div>
            <!--row-->
            @include('admin.includes.alerts')

            <form action="{{ route('typeOccurrences.destroy', $typeOccurrence->id) }}" method="POST">
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
