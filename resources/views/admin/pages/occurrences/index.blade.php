@extends('adminlte::page')
@section('title', 'Ocorrências')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => 'Ocorrências',
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Ocorrências'],
        ],
        'actionsHtml' => '<a href="'.route('occurrences.create').'" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Adicionar</a>',
    ])
@stop

@section('content')
    <div class="card">
        @include('admin.includes.alerts')

        @include('admin.includes.search-toolbar', [
            'action' => route('occurrences.search'),
            'placeholder' => 'Título, protocolo ou solicitante…',
            'filters' => $filters ?? [],
        ])

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover ciop-table mb-0">
                    <thead>
                        <tr>
                            <th>Protocolo</th>
                            <th>Título</th>
                            <th>Solicitante</th>
                            <th>Tipo</th>
                            <th>Prioridade</th>
                            <th>Órgão</th>
                            <th>Status</th>
                            <th>Atualizado</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($occurrences as $occurrence)
                            <tr>
                                <td>{{ $occurrence->protocol ?? '—' }}</td>
                                <td>{{ $occurrence->title }}</td>
                                <td>{{ $occurrence->name ?? '—' }}</td>
                                <td>{{ $occurrence->nameType ?? '—' }}</td>
                                <td>
                                    @if ($occurrence->priority)
                                        <span class="badge" style="background-color: {{ $occurrence->priority->color ?? '#6c757d' }}; color: #fff;">
                                            {{ $occurrence->priority->name }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $occurrence->nameIssuings ?? '—' }}</td>
                                <td>
                                    @php
                                        $statusName = $occurrence->nameStatus ?? '—';
                                        $badgeClass = match (strtolower($statusName)) {
                                            'aberta', 'aberto' => 'badge-warning',
                                            'em atendimento', 'em andamento' => 'badge-info',
                                            'finalizada', 'concluída', 'resolvida' => 'badge-success',
                                            'cancelada' => 'badge-secondary',
                                            default => 'badge-light text-dark',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ $statusName }}</span>
                                </td>
                                <td>{{ $occurrence->updated_at ? $occurrence->updated_at->format('d/m/Y H:i') : '—' }}</td>
                                <td>
                                    <div class="ciop-actions">
                                        <a href="{{ route('occurrences.show', $occurrence->id) }}" class="btn btn-outline-info btn-sm" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('occurrences.edit', $occurrence->id) }}" class="btn btn-outline-warning btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if (auth()->user()->isAdmin() || auth()->user()->driver)
                                            <a href="{{ route('driver.dashboard') }}" class="btn btn-outline-secondary btn-sm" title="Painel do motorista">
                                                <i class="fas fa-truck"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="ciop-empty">Nenhum registro encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            @if (isset($filters))
                {!! $occurrences->appends($filters)->links() !!}
            @else
                {!! $occurrences->links() !!}
            @endif
        </div>
    </div>
@endsection
