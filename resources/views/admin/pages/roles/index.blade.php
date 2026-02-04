@extends('adminlte::page')
@section('title', 'Cargos')

@section('content_header')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Painel de Controle</a> </li>
        <li class="breadcrumb-item active"><a href="{{ route('roles.index') }}">Cargos</a> </li>
    </ol>

    <h1 class="m-0 text-dark">Cargos
        <a href="{{ route('roles.create') }}" class="btn btn-primary mr-5">
            <i class="fas fa-save"></i>
            <span class=m-4>Adicionar</span>
        </a>
    </h1>
@stop

@section('content')
    <div class="card">
        @include('admin.includes.alerts')

        <div class="card-header">
            <form action="{{ route('roles.search') }}" method="POST" class="form form-inline">
                @csrf
                <div class="form-group">
                    <input type="text" class="form-control mr-2" name="filter" placeholder="Nome"
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
                        <th width="250px">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $role)
                        <tr>
                            <td>{{ $role->name }}</td>
                            <td style="width: 10px">
                                <a href="{{ route('roles.edit', $role->id) }}" title="Alterar" alt="Alterar"
                                    class="btn btn-warning"><i class="fas fa-edit"></i></a>
                                <a href="{{ route('roles.show', $role->id) }}" title="Visualizar" alt="Visualizar"
                                    class="btn btn-info"><i class="fas fa-search"></i></a>
                                <a href="{{ route('roles.permissions', $role->id) }}" title="Permissões" alt="Permissões"
                                    class="btn btn-warning">
                                    <i class="fas fa-lock"></i>
                                </a>

                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            @if (isset($filters))
                {!! $roles->appends($filters)->links() !!}
            @else
                {!! $roles->links() !!}
            @endif
        </div>
    </div>
@stop
