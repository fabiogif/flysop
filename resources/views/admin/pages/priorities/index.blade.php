@extends('adminlte::page')
@section('title', 'Prioridades')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => 'Prioridades',
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Prioridades'],
        ],
        'actionsHtml' => '<a href="'.route('priorities.create').'" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Adicionar</a>',
    ])
@stop

@section('content')
    <div class="card">
        @include('admin.includes.alerts')

        @include('admin.includes.search-toolbar', [
            'action' => route('priorities.search'),
            'placeholder' => 'Nome',
            'filters' => $filters ?? [],
        ])

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover ciop-table mb-0">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Peso</th>
                            <th>Cor</th>
                            <th>SLA padrão</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($priorities as $priority)
                            <tr>
                                <td>{{ $priority->name }}</td>
                                <td>{{ $priority->weight }}</td>
                                <td>
                                    @if ($priority->color)
                                        <span class="ciop-color-swatch" style="background-color: {{ $priority->color }};"></span>
                                        {{ $priority->color }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $priority->default_sla_hours ? $priority->default_sla_hours . 'h' : '—' }}</td>
                                <td>
                                    <div class="ciop-actions">
                                        <a href="{{ route('priorities.show', $priority->id) }}" class="btn btn-outline-info btn-sm" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('priorities.edit', $priority->id) }}" class="btn btn-outline-warning btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="ciop-empty">Nenhum registro encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            @if (isset($filters))
                {!! $priorities->appends($filters)->links() !!}
            @else
                {!! $priorities->links() !!}
            @endif
        </div>
    </div>
@endsection
