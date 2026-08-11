@extends('adminlte::page')
@section('title', 'Tipo de Ocorrência')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => 'Tipo de Ocorrência',
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Tipo de Ocorrência'],
        ],
        'actionsHtml' => '<a href="'.route('typeOccurrences.create').'" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Adicionar</a>',
    ])
@stop

@section('content')
    <div class="card">
        @include('admin.includes.alerts')

        @include('admin.includes.search-toolbar', [
            'action' => route('typeOccurrences.search'),
            'placeholder' => 'Nome',
            'filters' => $filters ?? [],
        ])

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover ciop-table mb-0">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($typeOccurrences as $typeOccurrence)
                            <tr>
                                <td>{{ $typeOccurrence->name }}</td>
                                <td>
                                    <div class="ciop-actions">
                                        <a href="{{ route('typeOccurrences.show', $typeOccurrence->id) }}" class="btn btn-outline-info btn-sm" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('typeOccurrences.edit', $typeOccurrence->id) }}" class="btn btn-outline-warning btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="ciop-empty">Nenhum registro encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            @if (isset($filters))
                {!! $typeOccurrences->appends($filters)->links() !!}
            @else
                {!! $typeOccurrences->links() !!}
            @endif
        </div>
    </div>
@endsection
