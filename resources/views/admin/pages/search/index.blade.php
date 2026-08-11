@extends('adminlte::page')
@section('title', 'Busca')

@section('content_header')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Painel de Controle</a> </li>
        <li class="breadcrumb-item active"><a href="{{ route('search.index') }}">Busca</a> </li>
    </ol>

    <h1 class="m-0 text-dark">Busca global</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <form action="{{ route('search.index') }}" method="GET" class="form form-inline">
                <div class="form-group flex-grow-1">
                    <input type="text" class="form-control mr-2 w-75" name="q" placeholder="Protocolo, título, nome ou descrição da ocorrência…"
                        value="{{ $term }}" autofocus>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-search"></i>
                        <span class=m-4>Buscar</span>
                    </button>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            @if ($term === '')
                <p class="text-muted p-3 mb-0">Digite um termo para buscar em protocolo, título, nome e descrição de ocorrências.</p>
            @else
                <table class="table table-condensed mb-0">
                    <thead>
                        <tr>
                            <th>Protocolo</th>
                            <th>Título</th>
                            <th>Tipo</th>
                            <th>Status</th>
                            <th width="120px">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($results as $occurrence)
                            <tr>
                                <td>{{ $occurrence->protocol ?? '—' }}</td>
                                <td>{{ $occurrence->title }}</td>
                                <td>{{ $occurrence->typeOccurrence->name ?? '—' }}</td>
                                <td>{{ $occurrence->statusOccurence->name ?? '—' }}</td>
                                <td><a href="{{ route('occurrences.show', $occurrence->id) }}" class="btn btn-info btn-sm">Ver</a></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">Nenhum resultado para "{{ $term }}".</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>
        @if ($results)
            <div class="card-footer">
                {!! $results->links() !!}
            </div>
        @endif
    </div>
@stop
