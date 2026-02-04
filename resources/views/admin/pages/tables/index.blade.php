@extends('adminlte::page')
@section('title', 'Mesas')

@section('content_header')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Painel de Controle</a> </li>
        <li class="breadcrumb-item active"><a href="{{ route('tables.index') }}">Mesas</a> </li>
    </ol>

    <h1 class="m-0 text-dark">Mesas
        <a href="{{ route('tables.create') }}" class="btn btn-primary mr-5">
            <i class="fas fa-save"></i>
            <span class=m-4>Adicionar</span>
        </a>
    </h1>
@stop

@section('content')
    <div class="card">
        @include('admin.includes.alerts')

        <div class="card-header">
            <form action="{{ route('tables.search') }}" method="POST" class="form form-inline">
                @csrf
                <div class="form-group">
                    <input type="text" class="form-control mr-2" name="filter" placeholder="Nome ou Descrição"
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
                        <th>Identificador</th>
                        <th>Descrição</th>
                        <th width="150px">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tables as $table)
                        <tr>
                            <td>{{ $table->identify }}</td>
                            <td>{{ $table->description }}</td>
                            <td style="width: 10px">
                                <a href="{{ route('tables.edit', $table->id) }}" class="btn btn-warning">Alterar</a>
                                <a href="{{ route('tables.show', $table->id) }}" class="btn btn-info">Visualizar</a>
                                </a>

                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            @if (isset($filters))
                {!! $tables->appends($filters)->links() !!}
            @else
                {!! $tables->links() !!}
            @endif
        </div>
    </div>
@stop
