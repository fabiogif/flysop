@extends('adminlte::page')
@section('title', 'Motoristas')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => 'Motoristas',
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Motoristas'],
        ],
        'actionsHtml' => '<a href="'.route('drivers.create').'" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Adicionar</a>',
    ])
@stop

@section('content')
    <div class="card">
        @include('admin.includes.alerts')

        @include('admin.includes.search-toolbar', [
            'action' => route('drivers.search'),
            'placeholder' => 'Nome ou e-mail',
            'filters' => $filters ?? [],
        ])

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover ciop-table mb-0">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Telefone</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($drivers as $driver)
                            <tr>
                                <td>{{ $driver->name }}</td>
                                <td>{{ $driver->email ?? '—' }}</td>
                                <td>{{ $driver->phone ?? '—' }}</td>
                                <td>
                                    @php
                                        $labels = \App\Models\Driver::statusLabels();
                                        $badge = match ($driver->status) {
                                            'disponivel' => 'badge-success',
                                            'em_deslocamento' => 'badge-info',
                                            'em_atendimento' => 'badge-warning',
                                            default => 'badge-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badge }}">{{ $labels[$driver->status] ?? $driver->status }}</span>
                                </td>
                                <td>
                                    <div class="ciop-actions">
                                        <a href="{{ route('drivers.show', $driver->id) }}" class="btn btn-outline-info btn-sm" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('drivers.edit', $driver->id) }}" class="btn btn-outline-warning btn-sm" title="Editar">
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
                {!! $drivers->appends($filters)->links() !!}
            @else
                {!! $drivers->links() !!}
            @endif
        </div>
    </div>
@endsection
