@extends('adminlte::page')
@section('title', 'Permissões do Cargo')

@section('content_header')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Painel de Controle</a> </li>
        <li class="breadcrumb-item active"><a href="{{ route('roles.index') }}">Permissões do Cargo</a> </li>
    </ol>

    <h1 class="m-0 text-dark">Permissões do Cargo - <b>{{ $role->name }}</b>
        <a href="{{ route('roles.permissions.available', $role->id) }}" class="btn btn-primary mr-5">
            <i class="fas fa-save"></i>
            <span class=m-4>Adicionar</span>
        </a>
    </h1>
@stop

@section('content')
    @include('admin.includes.alerts')

    <div class="card">
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
                        <th width="50px">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($permissions as $permission)
                        <tr>
                            <td>{{ $permission->name }}</td>
                            <td style="width: 10px">
                                <a href="{{ route('roles.permissions.detach', [$role->id, $permission->id]) }}"
                                    class="btn btn-danger">Desvincular</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            @if (isset($filters))
                {!! $permissions->appends($filters)->links() !!}
            @else
                {!! $permissions->links() !!}
            @endif
        </div>
    </div>
@stop
