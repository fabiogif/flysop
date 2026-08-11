@extends('adminlte::page')
@section('title', 'Relatórios')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => 'Relatórios',
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Relatórios'],
        ],
    ])
@stop

@section('content')
    @include('admin.includes.alerts')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">Gerar novo relatório</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('reports.store') }}" method="POST" class="form-row align-items-end">
                @csrf
                <div class="form-group col-md-3">
                    <label class="small mb-1">Tipo</label>
                    <select name="type" class="form-control form-control-sm" required>
                        @foreach ($typeLabels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label class="small mb-1">Status</label>
                    <select name="status_occurrences_id" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach ($filterOptions['statusOccurrences'] as $status)
                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label class="small mb-1">Tipo de ocorrência</label>
                    <select name="type_occurrences_id" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach ($filterOptions['typeOccurrences'] as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label class="small mb-1">De</label>
                    <input type="date" name="date_from" class="form-control form-control-sm">
                </div>
                <div class="form-group col-md-2">
                    <label class="small mb-1">Até</label>
                    <input type="date" name="date_to" class="form-control form-control-sm">
                </div>
                <div class="form-group col-md-1 mb-0">
                    <button type="submit" class="btn btn-sm btn-primary btn-block">Gerar</button>
                </div>
            </form>
            <p class="text-muted small mb-0 mt-2">
                Filtros (status/tipo/data) valem só para “Listagem de ocorrências”.
                “Tempo médio por etapa” considera todas as ocorrências.
            </p>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h3 class="card-title mb-0">Meus relatórios</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover ciop-table mb-0">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Status</th>
                            <th>Pedido em</th>
                            <th>Pronto em</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reports as $report)
                            <tr>
                                <td>{{ $typeLabels[$report->type] ?? $report->type }}</td>
                                <td>
                                    @if ($report->status === 'ready')
                                        <span class="badge badge-success">Pronto</span>
                                    @elseif ($report->status === 'failed')
                                        <span class="badge badge-danger" title="{{ $report->error }}">Falhou</span>
                                    @else
                                        <span class="badge badge-secondary">Processando…</span>
                                    @endif
                                </td>
                                <td>{{ $report->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $report->ready_at ? $report->ready_at->format('d/m/Y H:i') : '—' }}</td>
                                <td>
                                    <div class="ciop-actions">
                                        @if ($report->status === 'ready')
                                            <a href="{{ route('reports.download', $report->id) }}" class="btn btn-outline-info btn-sm" title="Baixar">
                                                <i class="fas fa-download"></i> Baixar
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="ciop-empty">Nenhum relatório gerado ainda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {!! $reports->links() !!}
        </div>
    </div>
@endsection
