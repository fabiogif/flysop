@extends('adminlte::page')

@section('title', "Detalhes Usuário { $user->name }")

@section('content_header')
    <h1 class="m-0 text-dark">Detalhes Usuário <b>{{ $user->name }}</b></h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @csrf
            <div class="row">
                <ul>
                    <li><b>Nome:</b> {{ $user->name }}</li>
                    <li><b>E-mail:</b> {{ $user->email }}</li>
                </ul>
            </div>
            <!--row-->
            @include('admin.includes.alerts')

            <form action="{{ route('users.destroy', $user->id) }}" method="POST">
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
