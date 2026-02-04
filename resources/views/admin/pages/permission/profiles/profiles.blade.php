@extends('adminlte::page')
@section('title', 'Perfil da permissão')

@section('content_header')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Painel de Controle</a> </li>
        <li class="breadcrumb-item active"><a href="{{ route('permission.index') }}">Permissões</a>

        </li>
    </ol>

    <h1 class="m-0 text-dark">Perfil da permissão <b>{{ $permission->name }}</b></h1>
@stop

@section('content')
    @include('admin.includes.alerts')

    <div class="card">
        <div class="card-header">
            <form action="{{ route('profiles.search') }}" method="POST" class="form form-inline">
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
                        <th width="50px">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($profiles as $profile)
                        <tr>
                            <td>{{ $profile->name }}</td>
                            <td style="width: 10px">
                                <a href="{{ route('profiles.permissions.detach', [$profile->id, $permission->id]) }}"
                                    class="btn btn-danger">Desvincular</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            @if (isset($filters))
                {!! $profiles->appends($filters)->links() !!}
            @else
                {!! $profiles->links() !!}
            @endif
        </div>
    </div>
@stop
