@extends('adminlte::page')

@section('title', "Detalhes Departamento { $department->name }")

@section('content_header')
    <h1 class="m-0 text-dark">Detalhes do Departamento <b>{{ $department->name }}</b></h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @csrf
            <div class="row">
                <ul>
                    <li><b>Nome:</b> {{ $department->name }}</li>
                    <li>
                        <b>Equipes:</b>
                        @forelse ($department->teams as $team)
                            <span class="badge badge-secondary">{{ $team->name }}</span>
                        @empty
                            —
                        @endforelse
                    </li>
                </ul>
            </div>
            <!--row-->
            @include('admin.includes.alerts')

            <form action="{{ route('departments.destroy', $department->id) }}" method="POST">
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
