@extends('adminlte::page')
@section('title', 'Permissões')

@section('content_header')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Painel de Controle</a> </li>
        <li class="breadcrumb-item active"><a href="{{ route('permission.index') }}">Permissões</a> </li>
    </ol>

    <h1 class="m-0 text-dark">Permissões
        @can('update', Mode::class)
            <a href="{{ route('permission.create') }}" class="btn btn-primary mr-5">
                <i class="fas fa-save"></i>
                <span class=m-4>Adicionar</span>
            </a>
        @endcan
    </h1>
@stop

@section('content')
    <div class="card">
        @include('admin.includes.alerts')
        <div class="card-header">
            <form action="{{ route('permission.search') }}" method="POST" class="form form-inline">
                @csrf
                <div class="form-group">
                    <input type="text" class="form-control mr-2" name="filter" placeholder="Nome ou Preço"
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
                        <th>Descrição</th>
                        <th width="200px">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($permissions as $permission)
                        <tr>
                            <td>{{ $permission->name }}</td>
                            <td>{{ $permission->description }}</td>
                            <td style="width: 10px">
                                <a href="{{ route('permission.edit', $permission->id) }}" title="Alterar" alt="Alterar"
                                    class="btn btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('permission.show', $permission->id) }}" title="Visualizar"
                                    alt="Visualizar" class="btn btn-info">
                                    <i class="fas fa-search"></i>
                                </a>
                                <a href="{{ route('permission.profiles', $permission->id) }}" title="Perfil" alt="Perfil"
                                    class="btn btn-info">
                                    <i class="fas fa-address-book "></i>
                                </a>
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
