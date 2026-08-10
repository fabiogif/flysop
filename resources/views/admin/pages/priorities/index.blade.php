@extends('adminlte::page')
@section('title', 'Prioridades')

@section('content_header')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Painel de Controle</a> </li>
        <li class="breadcrumb-item active"><a href="{{ route('priorities.index') }}">Prioridades</a> </li>
    </ol>

    <h1 class="m-0 text-dark">Prioridades
        <a href="{{ route('priorities.create') }}" class="btn btn-primary mr-5">
            <i class="fas fa-save"></i>
            <span class=m-4>Adicionar</span>
        </a>
    </h1>
@stop

@section('content')
    <div class="card">
        @include('admin.includes.alerts')

        <div class="card-header">
            <form action="{{ route('priorities.search') }}" method="POST" class="form form-inline">
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
                        <th>Peso</th>
                        <th>Cor</th>
                        <th>SLA padrão</th>
                        <th width="250px">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($priorities as $priority)
                        <tr>
                            <td>{{ $priority->name }}</td>
                            <td>{{ $priority->weight }}</td>
                            <td>
                                @if ($priority->color)
                                    <span class="badge" style="background-color: {{ $priority->color }};">&nbsp;&nbsp;&nbsp;</span>
                                    {{ $priority->color }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $priority->default_sla_hours ? $priority->default_sla_hours . 'h' : '—' }}</td>
                            <td style="width: 10px">
                                <a href="{{ route('priorities.edit', $priority->id) }}" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                                <a href="{{ route('priorities.show', $priority->id) }}" class="btn btn-info"><i class="fas fa-search"></i></a>
                            </td>
                        </tr>
                    @endforeach
                    @if (count($priorities) == 0)
                        <td>Não existe informações</td>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            @if (isset($filters))
                {!! $priorities->appends($filters)->links() !!}
            @else
                {!! $priorities->links() !!}
            @endif
        </div>
    </div>
@stop
