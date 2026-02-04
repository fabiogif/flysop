@extends('adminlte::page')
@section('title', 'Motoristas')

@section('content_header')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Painel de Controle</a></li>
        <li class="breadcrumb-item active">Motoristas</li>
    </ol>
    <h1 class="m-0 text-dark">Motoristas
        <a href="{{ route('drivers.create') }}" class="btn btn-primary mr-5">
            <i class="fas fa-plus"></i> Adicionar
        </a>
    </h1>
@stop

@section('content')
    <div class="card">
        @include('admin.includes.alerts')
        <div class="card-header">
            <form action="{{ route('drivers.search') }}" method="POST" class="form form-inline">
                @csrf
                <div class="form-group">
                    <input type="text" class="form-control mr-2" name="filter" placeholder="Nome ou e-mail"
                        value="{{ $filters['filter'] ?? '' }}">
                    <button type="submit" class="btn btn-info"><i class="fas fa-search"></i> Pesquisar</button>
                </div>
            </form>
        </div>
        <div class="card-body">
            <table class="table table-condensed">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Telefone</th>
                        <th>Status</th>
                        <th width="180">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($drivers as $driver)
                        <tr>
                            <td>{{ $driver->name }}</td>
                            <td>{{ $driver->email ?? '—' }}</td>
                            <td>{{ $driver->phone ?? '—' }}</td>
                            <td>
                                @php
                                    $labels = \App\Models\Driver::statusLabels();
                                    $badge = match($driver->status) {
                                        'disponivel' => 'badge-success',
                                        'em_deslocamento' => 'badge-info',
                                        'em_atendimento' => 'badge-warning',
                                        default => 'badge-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $badge }}">{{ $labels[$driver->status] ?? $driver->status }}</span>
                            </td>
                            <td>
                                <a href="{{ route('drivers.edit', $driver->id) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                <a href="{{ route('drivers.show', $driver->id) }}" class="btn btn-info btn-sm"><i class="fas fa-search"></i></a>
                            </td>
                        </tr>
                    @endforeach
                    @if ($drivers->isEmpty())
                        <tr><td colspan="5">Nenhum motorista cadastrado.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            @if (isset($filters))
                {!! $drivers->appends($filters)->links() !!}
            @else
                {!! $drivers->links() !!}
            @endif
        </div>
    </div>
@stop
