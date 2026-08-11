@extends('adminlte::page')
@section('title', 'Status de Ocorrência')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => 'Status de Ocorrência',
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Status de Ocorrência'],
        ],
        'actionsHtml' => '<a href="'.route('statusOccurrences.create').'" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Adicionar</a>',
    ])
@stop

@section('content')
    <div class="card">
        @include('admin.includes.alerts')

        @include('admin.includes.search-toolbar', [
            'action' => route('statusOccurrences.search'),
            'placeholder' => 'Nome',
            'filters' => $filters ?? [],
        ])

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover ciop-table mb-0">
                    <thead>
                        <tr>
                            <th>Ordem</th>
                            <th>Nome</th>
                            <th>Terminal</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($statusOccurrences as $statusOccurrence)
                            <tr>
                                <td>{{ $statusOccurrence->sort_order }}</td>
                                <td>{{ $statusOccurrence->name }}</td>
                                <td>{{ $statusOccurrence->is_terminal ? 'Sim' : 'Não' }}</td>
                                <td>
                                    <div class="ciop-actions">
                                        <a href="{{ route('statusOccurrences.show', $statusOccurrence->id) }}" class="btn btn-outline-info btn-sm" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('statusOccurrences.edit', $statusOccurrence->id) }}" class="btn btn-outline-warning btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="ciop-empty">Nenhum registro encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            @if (isset($filters))
                {!! $statusOccurrences->appends($filters)->links() !!}
            @else
                {!! $statusOccurrences->links() !!}
            @endif
        </div>
    </div>
@endsection
