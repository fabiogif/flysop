@extends('adminlte::page')
@section('title', 'Auditoria')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => 'Registro de auditoria',
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Auditoria'],
        ],
    ])
@stop

@section('content')
    <div class="card">
        @include('admin.includes.alerts')

        <div class="card-header ciop-toolbar">
            <form method="GET" action="{{ route('audit.index') }}" class="ciop-search-form">
                <div class="input-group">
                    <input type="text" name="filter" class="form-control" placeholder="Buscar ação…"
                        value="{{ $filters['filter'] ?? '' }}" aria-label="Buscar ação">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-search"></i>
                            <span class="d-none d-sm-inline">Filtrar</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover ciop-table mb-0">
                    <thead>
                        <tr>
                            <th>Quando</th>
                            <th>Ação</th>
                            <th>Quem</th>
                            <th>Alvo</th>
                            <th>Detalhes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($activities as $activity)
                            <tr>
                                <td>{{ $activity->created_at?->format('d/m/Y H:i') }}</td>
                                <td><code>{{ $activity->description }}</code></td>
                                <td>{{ $activity->causer?->email ?? '—' }}</td>
                                <td>
                                    @if ($activity->subject)
                                        {{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ \Illuminate\Support\Str::limit(json_encode($activity->properties?->toArray() ?? [], JSON_UNESCAPED_UNICODE), 120) }}
                                    </small>
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
            {{ $activities->links() }}
        </div>
    </div>
@endsection
