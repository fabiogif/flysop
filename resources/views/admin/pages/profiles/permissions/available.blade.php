@extends('adminlte::page')
@section('title', 'Permissões disponíveis por perfil {{ $profile->name }}')

@section('content_header')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Painel de Controle</a> </li>
        <li class="breadcrumb-item active"><a href="{{ route('profiles.index') }}">Permissões disponíveis por perfil</a>
        </li>
    </ol>

    <h1 class="m-0 text-dark">Permissões disponíveis por perfil |<b>{{ $profile->name }}</b>
    </h1>
@stop

@section('content')
    <div class="card">
        @include('admin.includes.alerts')

        <div class="card-header">
            <form action="{{ route('profiles.permissions.available', $profile->id) }}" method="POST"
                class="form form-inline">
                @csrf
                <div class="form-group">
                    <input type="text" class="form-control mr-2" name="filter" placeholder="Permissões"
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
                        <th width="50px">#</th>
                        <th>Nome</th>
                    </tr>
                </thead>
                <tbody>
                    <form action="{{ route('profiles.permissions.attach', $profile->id) }}" method="POST">
                        @csrf
                        @foreach ($permissions as $permission)
                            <tr>
                                <td><input type="checkbox" name="permissions[]" value="{{ $permission->id }}"></td>
                                <td>{{ $permission->name }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="500">
                                <button type="submit" class="btn btn-success">Vincular</button>
                            </td>
                        </tr>
                    </form>
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
