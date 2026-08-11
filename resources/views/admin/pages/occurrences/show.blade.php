@extends('adminlte::page')

@section('title', "Detalhes Tipo de Ocorrência { $occurrences->title }")

@section('content_header')
<h1 class="m-0 text-dark">Detalhes da Ocorrência <b>{{ $occurrences->title }}</b></h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @csrf
            <div class="row">
                <ul>
                    <li><b>Protocolo:</b> {{ $occurrences->protocol ?? '—' }}</li>
                    <li><b>Nome:</b> {{ $occurrences->title }}</li>
                    <li>
                        <b>Prioridade:</b>
                        @if ($occurrences->priority)
                            <span class="badge" style="background-color: {{ $occurrences->priority->color ?? '#6c757d' }}; color: #fff;">{{ $occurrences->priority->name }}</span>
                        @else
                            —
                        @endif
                    </li>
                    <li>
                        <b>Prazo (SLA):</b>
                        {{ $occurrences->due_at ? $occurrences->due_at->format('d/m/Y H:i') : '—' }}
                        @if ($occurrences->due_at && $occurrences->due_at->isPast())
                            <span class="badge badge-danger">Vencido</span>
                        @endif
                    </li>
                    <li><b>Ultima Atualização:</b> {{ date('d/M/Y h:m:s', strtotime($occurrences->updated_at)) }}</li>
                    <li><b>E-mail:</b> {{ $occurrences->email }}</li>
                    <li><b>Telefone:</b> {{ $occurrences->phone }}</li>
                    <li><b>Endereço:</b> {{ $occurrences->address }}</li>
                    <li><b>Bairro/Cidade/UF:</b> {{ implode(' - ', array_filter([$occurrences->neighborhood, $occurrences->city, $occurrences->state])) ?: '—' }}</li>
                </ul>
            </div>

            @php
                $phaseLabels = ['antes' => 'Antes', 'depois' => 'Depois'];
            @endphp
            @foreach (['antes', 'depois', ''] as $phaseKey)
                @php
                    $imagensDaFase = $occurrencesImagens->filter(fn ($img) => ($img->phase ?? '') === $phaseKey);
                @endphp
                @if ($imagensDaFase->isNotEmpty())
                    <h5 class="mt-3">{{ $phaseLabels[$phaseKey] ?? 'Evidências (sem fase classificada)' }}</h5>
                    <div class="row">
                        @foreach ($imagensDaFase as $occurrencesImagen)
                            <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 mb-3">
                                <div style="padding:5px; background:#343a40!important; border-radius:12px;">
                                    <img style="border-radius:8px; width: 100%;"
                                        src="https://sopanexos.s3.amazonaws.com/{{ $occurrencesImagen->url }}"
                                        alt="{{ $occurrences->title }}" />
                                    <p class="text-white small mb-0 mt-1 px-1">
                                        {{ $occurrencesImagen->uploadedBy->name ?? 'Sistema' }} —
                                        {{ $occurrencesImagen->captured_at ? $occurrencesImagen->captured_at->format('d/m/Y H:i') : ($occurrencesImagen->created_at ? $occurrencesImagen->created_at->format('d/m/Y H:i') : '—') }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endforeach

            @include('admin.includes.alerts')

            <form action="{{ route('occurrences.destroy', $occurrences->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="far fa-trash-alt"></i>
                    <span class=m-4>Excluir</span>
                </button>
            </form>
        </div>
        <!--card-body-->
    </div>
    <!--card-->

    <div class="card mt-3">
        <div class="card-header">
            <span>Auditoria (campos alterados)</span>
        </div>
        <div class="card-body p-0">
            <table class="table table-condensed mb-0">
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
                            <td colspan="5">Sem alterações registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <span>Histórico de status</span>
        </div>
        <div class="card-body p-0">
            <table class="table table-condensed mb-0">
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
                            <td colspan="5">Sem histórico registrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($occurrences->driver_id)
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Rota do motorista <span id="driver-route-live-badge" class="badge badge-success ml-2"
                        style="display: none;">Ao vivo</span></span>
                <small class="text-muted" id="driver-route-updated">Atualização a cada 10s</small>
            </div>
            <div class="card-body p-0">
                <div id="occurrence-driver-route-map" style="height: 400px;"></div>
            </div>
        </div>
    @endif

    @if ($occurrences->driver_id)
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Rota do motorista</span>
            </div>
            <div class="card-body p-0">
                <driver-tracker occurrence-id="{{ $occurrences->id }}"
                    occurrence-lat="{{ $occurrences->latitude ?: '-12.95307' }}"
                    occurrence-lng="{{ $occurrences->longitude ?: '-38.49706' }}" :height="400">
                </driver-tracker>
            </div>
        </div>
    @endif

@endsection