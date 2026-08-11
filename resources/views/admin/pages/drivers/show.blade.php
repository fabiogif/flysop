@extends('adminlte::page')
@section('title', 'Motorista')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => $driver->name,
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Motoristas', 'url' => route('drivers.index')],
            ['label' => $driver->name],
        ],
        'actionsHtml' => '<a href="'.route('drivers.edit', $driver->id).'" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Editar</a>',
    ])
@stop

@section('content')
    @include('admin.includes.alerts')

    <div class="card">
        <div class="card-body">
            <dl class="ciop-detail-grid">
                <div class="ciop-detail-item">
                    <dt>Nome</dt>
                    <dd>{{ $driver->name }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>E-mail</dt>
                    <dd>{{ $driver->email ?? '—' }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>Telefone</dt>
                    <dd>{{ $driver->phone ?? '—' }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>CPF</dt>
                    <dd>{{ $driver->cpf ?? '—' }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>Status</dt>
                    <dd>
                        <span class="badge badge-{{ $driver->status === 'disponivel' ? 'success' : ($driver->status === 'em_atendimento' ? 'warning' : 'info') }}">
                            {{ \App\Models\Driver::statusLabels()[$driver->status] ?? $driver->status }}
                        </span>
                    </dd>
                </div>
            </dl>

            <h5 class="mt-4 mb-3">Ocorrências vinculadas</h5>
            @if ($driver->occurrences->isEmpty())
                <p class="text-muted mb-0">Nenhuma ocorrência vinculada.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover ciop-table mb-0">
                        <thead>
                            <tr>
                                <th>Ocorrência</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($driver->occurrences as $occ)
                                <tr>
                                    <td>
                                        <a href="{{ route('occurrences.show', $occ->id) }}">{{ $occ->title ?? $occ->name }}</a>
                                    </td>
                                    <td>{{ $occ->created_at ? $occ->created_at->format('d/m/Y') : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        <div class="card-footer ciop-detail-footer">
            <a href="{{ route('drivers.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <div class="ciop-detail-footer-spacer"></div>
        </div>
    </div>
@endsection
