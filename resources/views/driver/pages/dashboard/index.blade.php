@extends('adminlte::page')
@section('title', 'Painel do Motorista')

@section('content_header')
    <h1 class="m-0 text-dark">Painel do Motorista</h1>
@stop

@section('content')
    @include('admin.includes.alerts')

    <div class="row">
        <div class="col-lg-4 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $aguardando }}</h3>
                    <p>Aguardando aceitação</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
                <a href="{{ route('driver.occurrences.index') }}" class="small-box-footer">Ver ocorrências <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $emAndamento }}</h3>
                    <p>Em andamento</p>
                </div>
                <div class="icon"><i class="fas fa-truck"></i></div>
                <a href="{{ route('driver.occurrences.index') }}" class="small-box-footer">Ver ocorrências <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $occurrences->total() }}</h3>
                    <p>Total atribuídas</p>
                </div>
                <div class="icon"><i class="fas fa-list"></i></div>
                <a href="{{ route('driver.occurrences.index') }}" class="small-box-footer">Ver todas <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Ocorrências recentes</h3>
                </div>
                <div class="card-body p-0">
                    @if ($occurrences->count())
                        <ul class="list-group list-group-flush">
                            @foreach ($occurrences as $o)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <a href="{{ route('driver.occurrences.show', $o->id) }}">{{ $o->title ?: $o->name ?: 'Ocorrência #' . $o->id }}</a>
                                        <br>
                                        <span class="badge badge-secondary">{{ $o->statusOccurence?->name ?? '—' }}</span>
                                    </div>
                                    <div>
                                        @if ($o->statusOccurence && $o->statusOccurence->name === 'Aguardando aceitação' && $o->driver_id)
                                            <form action="{{ route('driver.occurrences.accept', $o->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Aceitar esta ocorrência?');">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm">Aceitar</button>
                                            </form>
                                            <form action="{{ route('driver.occurrences.reject', $o->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Recusar esta ocorrência?');">
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-sm">Recusar</button>
                                            </form>
                                        @else
                                            <a href="{{ route('driver.occurrences.show', $o->id) }}" class="btn btn-info btn-sm">Ver</a>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="p-3 text-muted mb-0">{{ auth()->user()->isAdmin() ? 'Nenhuma ocorrência encontrada.' : 'Nenhuma ocorrência atribuída a você.' }}</p>
                    @endif
                </div>
                @if ($occurrences->hasPages())
                    <div class="card-footer">{{ $occurrences->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@stop
