@extends('adminlte::page')
@section('title', 'Busca')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => 'Busca global',
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Busca'],
        ],
    ])
@stop

@section('content')
    <div class="card">
        <div class="card-header ciop-toolbar">
            <form action="{{ route('search.index') }}" method="GET" class="ciop-search-form">
                <div class="input-group">
                    <input type="text" class="form-control" name="q"
                        placeholder="Protocolo, título, nome ou descrição da ocorrência…"
                        value="{{ $term }}" autofocus aria-label="Buscar ocorrências">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-search"></i>
                            <span class="d-none d-sm-inline">Buscar</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            @if ($term === '')
                <p class="ciop-empty mb-0">Digite um termo para buscar em protocolo, título, nome e descrição de ocorrências.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover ciop-table mb-0">
                        <thead>
                            <tr>
                                <th>Protocolo</th>
                                <th>Título</th>
                                <th>Tipo</th>
                                <th>Status</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($results as $occurrence)
                                <tr>
                                    <td>{{ $occurrence->protocol ?? '—' }}</td>
                                    <td>{{ $occurrence->title }}</td>
                                    <td>{{ $occurrence->typeOccurrence->name ?? '—' }}</td>
                                    <td>{{ $occurrence->statusOccurence->name ?? '—' }}</td>
                                    <td>
                                        <div class="ciop-actions">
                                            <a href="{{ route('occurrences.show', $occurrence->id) }}" class="btn btn-outline-info btn-sm" title="Ver">
                                                <i class="fas fa-eye"></i> Ver
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="ciop-empty">Nenhum resultado para "{{ $term }}".</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if ($results)
            <div class="card-footer">
                {!! $results->links() !!}
            </div>
        @endif
    </div>
@endsection
