@extends('adminlte::page')
@section('title', 'Perfis')

@section('content_header')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Painel de Controle</a> </li>
        <li class="breadcrumb-item active"><a href="{{ route('profiles.index') }}">Perfis</a> </li>
    </ol>

    <h1 class="m-0 text-dark">Perfis
        <a href="{{ route('profiles.create') }}" class="btn btn-primary mr-5">
            <i class="fas fa-save"></i>
            <span class=m-4>Adicionar</span>
        </a>
    </h1>
@stop

@section('content')
    <div class="card">
        @include('admin.includes.alerts')

        <div class="card-header">
            <form action="{{ route('profiles.search') }}" method="POST" class="form form-inline">
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
                        <th width="200px">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($profiles as $profile)
                        <tr>
                            <td>{{ $profile->name }}</td>
                            <td style="width: 10px">
                                <a href="{{ route('profiles.edit', $profile->id) }}" title="Alterar" alt="Alterar"
                                    class="btn btn-warning"><i class="fas fa-edit"></i></a>
                                <a href="{{ route('profiles.show', $profile->id) }}" title="Visualizar" alt="Visualizar"
                                    class="btn btn-info"><i class="fas fa-search"></i></a>
                                <a href="{{ route('profiles.permissions', $profile->id) }}" class="btn btn-warning">
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
                {!! $profiles->appends($filters)->links() !!}
            @else
                {!! $profiles->links() !!}
            @endif
        </div>
    </div>
@stop
