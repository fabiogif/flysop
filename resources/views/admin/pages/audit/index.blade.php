@extends('adminlte::page')
@section('title', 'Auditoria')

@section('content_header')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Painel</a></li>
        <li class="breadcrumb-item active">Auditoria</li>
    </ol>
    <h1 class="m-0 text-dark">Registro de auditoria</h1>
@stop

@section('content')
    <div class="card">
        @include('admin.includes.alerts')
        <div class="card-header">
            <form method="GET" action="{{ route('audit.index') }}" class="form-inline">
                <input type="text" name="filter" class="form-control mr-2" placeholder="Buscar ação…"
                    value="{{ $filters['filter'] ?? '' }}">
                <button type="submit" class="btn btn-info"><i class="fas fa-search"></i> Filtrar</button>
            </form>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
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
                            <td colspan="5" class="text-center text-muted">Nenhum evento registrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $activities->links() }}</div>
    </div>
@stop
