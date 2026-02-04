@extends('adminlte::page')
@section('title', 'Usuários')

@section('content_header')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Painel de Controle</a> </li>
        <li class="breadcrumb-item active"><a href="{{ route('users.index') }}">Usuários</a> </li>
    </ol>

    <h1 class="m-0 text-dark">Usuários
        <a href="{{ route('users.create') }}" class="btn btn-primary mr-5">
            <i class="fas fa-save"></i>
            <span class=m-4>Adicionar</span>
        </a>
    </h1>
@stop

@section('content')
    <div class="card">
        @include('admin.includes.alerts')

        <div class="card-header">
            <form action="{{ route('users.search') }}" method="POST" class="form form-inline">
                @csrf
                <div class="form-group">
                    <input type="text" class="form-control mr-2" name="filter" placeholder="Nome e Email"
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
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th width="200px">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td style="width: 10px">
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning"><i
                                        class="fas fa-edit"></i></a>
                                <a href="{{ route('users.show', $user->id) }}" class="btn btn-info"><i
                                        class="fas fa-search"></i></a>
                                <a href="{{ route('users.roles', $user->id) }}" class="btn btn-info" alt="Cargos"
                                    title="Cargos">
                                    <i class="fas fa-address-card"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            @if (isset($filters))
                {!! $users->appends($filters)->links() !!}
            @else
                {!! $users->links() !!}
            @endif
        </div>
    </div>
@stop
