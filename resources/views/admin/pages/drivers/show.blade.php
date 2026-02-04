@extends('adminlte::page')
@section('title', 'Motorista')

@section('content_header')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Painel de Controle</a></li>
        <li class="breadcrumb-item"><a href="{{ route('drivers.index') }}">Motoristas</a></li>
        <li class="breadcrumb-item active">{{ $driver->name }}</li>
    </ol>
    <h1 class="m-0 text-dark">Motorista: {{ $driver->name }}</h1>
@stop

@section('content')
    <div class="card">
        @include('admin.includes.alerts')
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-2">Nome</dt>
                <dd class="col-sm-10">{{ $driver->name }}</dd>
                <dt class="col-sm-2">E-mail</dt>
                <dd class="col-sm-10">{{ $driver->email ?? '—' }}</dd>
                <dt class="col-sm-2">Telefone</dt>
                <dd class="col-sm-10">{{ $driver->phone ?? '—' }}</dd>
                <dt class="col-sm-2">CPF</dt>
                <dd class="col-sm-10">{{ $driver->cpf ?? '—' }}</dd>
                <dt class="col-sm-2">Status</dt>
                <dd class="col-sm-10">
                    <span class="badge badge-{{ $driver->status === 'disponivel' ? 'success' : ($driver->status === 'em_atendimento' ? 'warning' : 'info') }}">
                        {{ \App\Models\Driver::statusLabels()[$driver->status] ?? $driver->status }}
                    </span>
                </dd>
            </dl>
            <hr>
            <h5>Ocorrências vinculadas</h5>
            @if ($driver->occurrences->isEmpty())
                <p class="text-muted">Nenhuma ocorrência vinculada.</p>
            @else
                <ul class="list-group">
                    @foreach ($driver->occurrences as $occ)
                        <li class="list-group-item d-flex justify-content-between">
                            <a href="{{ route('occurrences.show', $occ->id) }}">{{ $occ->title ?? $occ->name }}</a>
                            <span class="badge badge-secondary">{{ $occ->created_at->format('d/m/Y') }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
        <div class="card-footer">
            <a href="{{ route('drivers.edit', $driver->id) }}" class="btn btn-warning">Editar</a>
            <a href="{{ route('drivers.index') }}" class="btn btn-secondary">Voltar</a>
        </div>
    </div>
@stop
