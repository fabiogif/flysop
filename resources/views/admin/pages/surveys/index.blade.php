@extends('adminlte::page')
@section('title', 'Pesquisas')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => 'Pesquisas',
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Pesquisas'],
        ],
        'actionsHtml' => '<a href="'.route('surveys.create').'" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Adicionar</a>',
    ])
@stop

@section('content')
    <div class="card">
        @include('admin.includes.alerts')

        @include('admin.includes.search-toolbar', [
            'action' => route('surveys.search'),
            'placeholder' => 'Título ou descrição…',
            'filters' => $filters ?? [],
        ])

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover ciop-table mb-0">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Status</th>
                            <th>Perguntas</th>
                            <th>Respostas</th>
                            <th>Atualizado</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($surveys as $survey)
                            <tr>
                                <td>{{ $survey->title }}</td>
                                <td>
                                    @if ($survey->is_active)
                                        <span class="badge badge-success">Ativa</span>
                                    @else
                                        <span class="badge badge-secondary">Inativa</span>
                                    @endif
                                </td>
                                <td>{{ $survey->questions_count }}</td>
                                <td>{{ $survey->responses_count }}</td>
                                <td>{{ $survey->updated_at ? $survey->updated_at->format('d/m/Y H:i') : '—' }}</td>
                                <td>
                                    <div class="ciop-actions">
                                        <a href="{{ route('surveys.show', $survey->id) }}" class="btn btn-outline-info btn-sm" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('surveys.edit', $survey->id) }}" class="btn btn-outline-warning btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('surveys.responses', $survey->id) }}" class="btn btn-outline-secondary btn-sm" title="Respostas">
                                            <i class="fas fa-inbox"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="ciop-empty">Nenhuma pesquisa cadastrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            @if (isset($filters))
                {!! $surveys->appends($filters)->links() !!}
            @else
                {!! $surveys->links() !!}
            @endif
        </div>
    </div>
@endsection
