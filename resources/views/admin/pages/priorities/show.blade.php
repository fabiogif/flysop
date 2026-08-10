@extends('adminlte::page')

@section('title', "Detalhes Prioridade { $priority->name }")

@section('content_header')
    <h1 class="m-0 text-dark">Detalhes da Prioridade <b>{{ $priority->name }}</b></h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @csrf
            <div class="row">
                <ul>
                    <li><b>Nome:</b> {{ $priority->name }}</li>
                    <li><b>Peso:</b> {{ $priority->weight }}</li>
                    <li><b>Cor:</b> {{ $priority->color ?? '—' }}</li>
                    <li><b>SLA padrão:</b> {{ $priority->default_sla_hours ? $priority->default_sla_hours . ' horas' : '—' }}</li>
                </ul>
            </div>
            <!--row-->
            @include('admin.includes.alerts')

            <form action="{{ route('priorities.destroy', $priority->id) }}" method="POST">
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
