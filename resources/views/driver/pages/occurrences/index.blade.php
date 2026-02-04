@extends('adminlte::page')
@section('title', 'Minhas Ocorrências')

@section('content_header')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('driver.dashboard') }}">Painel Motorista</a></li>
        <li class="breadcrumb-item active">Ocorrências</li>
    </ol>
    <h1 class="m-0 text-dark">Minhas Ocorrências</h1>
@stop

@section('content')
    @include('admin.includes.alerts')

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-condensed">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th>Atualizado em</th>
                        <th width="180">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($occurrences as $o)
                        <tr>
                            <td>{{ $o->title ?: '—' }}</td>
                            <td>{{ $o->name ?: '—' }}</td>
                            <td>{{ $o->typeOccurrence?->name ?? '—' }}</td>
                            <td>
                                @php
                                    $statusName = $o->statusOccurence ? $o->statusOccurence->name : '—';
                                    $sn = strtolower($statusName);
                                    if ($sn === 'aguardando aceitação') {
                                        $badgeClass = 'badge-warning';
                                    } elseif (in_array($sn, ['aceita', 'em deslocamento', 'em atendimento'], true)) {
                                        $badgeClass = 'badge-info';
                                    } elseif (in_array($sn, ['finalizada', 'concluída'], true)) {
                                        $badgeClass = 'badge-success';
                                    } elseif ($sn === 'recusada') {
                                        $badgeClass = 'badge-danger';
                                    } else {
                                        $badgeClass = 'badge-secondary';
                                    }
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $statusName }}</span>
                            </td>
                            <td>{{ $o->updated_at ? $o->updated_at->format('d/m/Y H:i') : '—' }}</td>
                            <td>
                                <a href="{{ route('driver.occurrences.show', $o->id) }}" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> Ver</a>
                                @if ($o->statusOccurence && $o->statusOccurence->name === 'Aguardando aceitação')
                                    <form action="{{ route('driver.occurrences.accept', $o->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Aceitar?');">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check"></i></button>
                                    </form>
                                    <form action="{{ route('driver.occurrences.reject', $o->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Recusar?');">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-times"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Nenhuma ocorrência atribuída a você.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($occurrences->hasPages())
            <div class="card-footer">{{ $occurrences->links() }}</div>
        @endif
    </div>
@stop
