@extends('adminlte::page')

@section('title', 'Detalhes da Ocorrência')

@section('content_header')
    <div class="ciop-page-head">
        <ol class="breadcrumb ciop-breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Painel de Controle</a></li>
            <li class="breadcrumb-item"><a href="{{ route('occurrences.index') }}">Ocorrências</a></li>
            <li class="breadcrumb-item active">{{ $occurrences->title ?? ($occurrences->protocol ?? 'Detalhe') }}</li>
        </ol>
        <div class="ciop-page-head-row">
            <div class="ciop-page-head-titles">
                <h1 class="ciop-page-title">Detalhes da Ocorrência</h1>
                <p class="ciop-page-subtitle mb-0">{{ $occurrences->protocol ?? $occurrences->title }}</p>
            </div>
            <div class="ciop-page-actions">
                <a href="{{ route('occurrences.pdf', $occurrences->id) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-file-pdf"></i> Baixar ficha (PDF)
                </a>
                @can('forget', $occurrences)
                    <form action="{{ route('occurrences.forget', $occurrences->id) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Remover os dados pessoais (nome, CPF, RG, e-mail, telefone) desta ocorrência? Esta ação é irreversível.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-user-slash"></i> Esquecer dados pessoais (LGPD)
                        </button>
                    </form>
                @endcan
                <a href="{{ route('occurrences.edit', $occurrences->id) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i> Editar
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    @include('admin.includes.alerts')

    @if ($occurrences->possibleDuplicateOf)
        <div class="alert alert-warning d-flex justify-content-between align-items-center">
            <span>
                <i class="fas fa-clone"></i>
                Possível duplicata de
                <a href="{{ route('occurrences.show', $occurrences->possibleDuplicateOf->id) }}">
                    {{ $occurrences->possibleDuplicateOf->protocol ?? ('#' . $occurrences->possibleDuplicateOf->id) }} — {{ $occurrences->possibleDuplicateOf->title }}
                </a>
                (mesmo tipo, a menos de 300m, criada nas últimas 48h).
            </span>
            <form action="{{ route('occurrences.dismiss-duplicate', $occurrences->id) }}" method="POST" class="ml-2">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary">Não é duplicata</button>
            </form>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <dl class="ciop-detail-grid">
                <div class="ciop-detail-item">
                    <dt>Protocolo</dt>
                    <dd>{{ $occurrences->protocol ?? '—' }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>Título</dt>
                    <dd>{{ $occurrences->title ?? '—' }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>Status</dt>
                    <dd>{{ $occurrences->statusOccurence->name ?? '—' }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>Tipo</dt>
                    <dd>{{ $occurrences->typeOccurrence->name ?? '—' }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>Órgão</dt>
                    <dd>{{ $occurrences->issuing->name ?? '—' }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>Prioridade</dt>
                    <dd>
                        @if ($occurrences->priority)
                            <span class="badge" style="background-color: {{ $occurrences->priority->color ?? '#6c757d' }}; color: #fff;">
                                {{ $occurrences->priority->name }}
                            </span>
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>SLA / Prazo</dt>
                    <dd>
                        {{ $occurrences->due_at ? $occurrences->due_at->format('d/m/Y H:i') : '—' }}
                        @if ($occurrences->due_at && $occurrences->due_at->isPast())
                            <span class="badge badge-danger">Vencido</span>
                        @endif
                    </dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>Solicitante</dt>
                    <dd>{{ $occurrences->name ?? '—' }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>E-mail</dt>
                    <dd>{{ $occurrences->email ?? '—' }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>Telefone</dt>
                    <dd>{{ $occurrences->phone ?? '—' }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>Motorista</dt>
                    <dd>{{ $occurrences->driver->name ?? '—' }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>Endereço</dt>
                    <dd>{{ $occurrences->address ?? '—' }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>Bairro / Cidade / UF</dt>
                    <dd>{{ implode(' — ', array_filter([$occurrences->neighborhood, $occurrences->city, $occurrences->state])) ?: '—' }}</dd>
                </div>
                <div class="ciop-detail-item ciop-detail-wide">
                    <dt>Descrição</dt>
                    <dd>{{ $occurrences->description ?? '—' }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>Atualizado</dt>
                    <dd>{{ $occurrences->updated_at ? $occurrences->updated_at->format('d/m/Y H:i') : '—' }}</dd>
                </div>
            </dl>

            @php
                $phaseLabels = ['antes' => 'Antes', 'depois' => 'Depois'];
            @endphp
            @foreach (['antes', 'depois', ''] as $phaseKey)
                @php
                    $imagensDaFase = $occurrencesImagens->filter(fn ($img) => ($img->phase ?? '') === $phaseKey);
                @endphp
                @if ($imagensDaFase->isNotEmpty())
                    <h5 class="mt-4 mb-3">{{ $phaseLabels[$phaseKey] ?? 'Evidências (sem fase classificada)' }}</h5>
                    <div class="row">
                        @foreach ($imagensDaFase as $occurrencesImagen)
                            <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                                <div class="ciop-evidence-card">
                                    <img src="https://sopanexos.s3.amazonaws.com/{{ $occurrencesImagen->url }}"
                                        alt="{{ $occurrences->title }}" />
                                    <p class="ciop-evidence-meta">
                                        {{ $occurrencesImagen->uploadedBy->name ?? 'Sistema' }} —
                                        {{ $occurrencesImagen->captured_at ? $occurrencesImagen->captured_at->format('d/m/Y H:i') : ($occurrencesImagen->created_at ? $occurrencesImagen->created_at->format('d/m/Y H:i') : '—') }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endforeach
        </div>
        <div class="card-footer ciop-detail-footer">
            <a href="{{ route('occurrences.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <div class="ciop-detail-footer-spacer"></div>
            <form action="{{ route('occurrences.destroy', $occurrences->id) }}" method="POST" class="d-inline"
                onsubmit="return confirm('Excluir esta ocorrência?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="far fa-trash-alt"></i> Excluir
                </button>
            </form>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <span>Auditoria (campos alterados)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover ciop-table mb-0">
                    <thead>
                        <tr>
                            <th>Quem</th>
                            <th>Quando</th>
                            <th>Campo</th>
                            <th>De</th>
                            <th>Para</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($occurrences->activities->sortByDesc('created_at') as $activity)
                            @php
                                $old = $activity->properties->get('old', []);
                                $attributes = $activity->properties->get('attributes', []);
                            @endphp
                            @forelse ($attributes as $field => $newValue)
                                <tr>
                                    <td>{{ $activity->causer->name ?? 'Sistema' }}</td>
                                    <td>{{ $activity->created_at ? $activity->created_at->format('d/m/Y H:i') : '—' }}</td>
                                    <td>{{ $field }}</td>
                                    <td>{{ $old[$field] ?? '—' }}</td>
                                    <td>{{ $newValue ?? '—' }}</td>
                                </tr>
                            @empty
                            @endforelse
                        @empty
                            <tr>
                                <td colspan="5" class="ciop-empty">Sem alterações registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <span>Histórico de status</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover ciop-table mb-0">
                    <thead>
                        <tr>
                            <th>De</th>
                            <th>Para</th>
                            <th>Alterado por</th>
                            <th>Data</th>
                            <th>Observação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($occurrences->statusHistory as $entry)
                            <tr>
                                <td>{{ $entry->fromStatus->name ?? '—' }}</td>
                                <td>{{ $entry->toStatus->name ?? '—' }}</td>
                                <td>{{ $entry->changedBy->name ?? 'Sistema' }}</td>
                                <td>{{ $entry->created_at ? $entry->created_at->format('d/m/Y H:i') : '—' }}</td>
                                <td>{{ $entry->note ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="ciop-empty">Sem histórico registrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if ($occurrences->driver_id)
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Rota do motorista</span>
            </div>
            <div class="card-body p-0">
                <driver-tracker occurrence-id="{{ $occurrences->id }}"
                    occurrence-lat="{{ $occurrences->latitude ?: '-12.95307' }}"
                    occurrence-lng="{{ $occurrences->longitude ?: '-38.49706' }}"
                    route-history-url="{{ route('occurrences.driver-route', $occurrences->id) }}" :height="400">
                </driver-tracker>
            </div>
        </div>
    @endif
@endsection
