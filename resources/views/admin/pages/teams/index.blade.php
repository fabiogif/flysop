@extends('adminlte::page')
@section('title', 'Equipes')

@section('content_header')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Painel de Controle</a> </li>
        <li class="breadcrumb-item active"><a href="{{ route('teams.index') }}">Equipes</a> </li>
    </ol>

    <h1 class="m-0 text-dark">Equipes
        <a href="{{ route('teams.create') }}" class="btn btn-primary mr-5">
            <i class="fas fa-save"></i>
            <span class=m-4>Adicionar</span>
        </a>
    </h1>
@stop

@section('content')
    <div class="card">
        @include('admin.includes.alerts')

        <div class="card-header">
            <form action="{{ route('teams.search') }}" method="POST" class="form form-inline">
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
                        <th>Departamento</th>
                        <th width="250px">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($teams as $team)
                        <tr>
                            <td>{{ $team->name }}</td>
                            <td>{{ $team->department->name ?? '—' }}</td>
                            <td style="width: 10px">
                                <a href="{{ route('teams.edit', $team->id) }}" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                                <a href="{{ route('teams.show', $team->id) }}" class="btn btn-info"><i class="fas fa-search"></i></a>
                            </td>
                        </tr>
                    @endforeach
                    @if (count($teams) == 0)
                        <td>Não existe informações</td>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            @if (isset($filters))
                {!! $teams->appends($filters)->links() !!}
            @else
                {!! $teams->links() !!}
            @endif
        </div>
    </div>
@stop
