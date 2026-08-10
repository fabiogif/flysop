@extends('adminlte::page')

@section('title', "Detalhes Equipe { $team->name }")

@section('content_header')
    <h1 class="m-0 text-dark">Detalhes da Equipe <b>{{ $team->name }}</b></h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @csrf
            <div class="row">
                <ul>
                    <li><b>Nome:</b> {{ $team->name }}</li>
                    <li><b>Departamento:</b> {{ $team->department->name ?? '—' }}</li>
                    <li>
                        <b>Motoristas:</b>
                        @forelse ($team->drivers as $driver)
                            <span class="badge badge-secondary">{{ $driver->name }}</span>
                        @empty
                            —
                        @endforelse
                    </li>
                </ul>
            </div>
            <!--row-->
            @include('admin.includes.alerts')

            <form action="{{ route('teams.destroy', $team->id) }}" method="POST">
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
