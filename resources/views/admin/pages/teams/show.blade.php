@extends('adminlte::page')

@section('title', 'Detalhes da Equipe')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => $team->name,
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Equipes', 'url' => route('teams.index')],
            ['label' => $team->name],
        ],
        'actionsHtml' => '<a href="'.route('teams.edit', $team->id).'" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Editar</a>',
    ])
@stop

@section('content')
    @include('admin.includes.alerts')

    <div class="card">
        <div class="card-body">
            <dl class="ciop-detail-grid">
                <div class="ciop-detail-item">
                    <dt>Nome</dt>
                    <dd>{{ $team->name }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>Departamento</dt>
                    <dd>{{ $team->department->name ?? '—' }}</dd>
                </div>
                <div class="ciop-detail-item ciop-detail-wide">
                    <dt>Motoristas</dt>
                    <dd>
                        @forelse ($team->drivers as $driver)
                            <span class="badge badge-secondary">{{ $driver->name }}</span>
                        @empty
                            —
                        @endforelse
                    </dd>
                </div>
            </dl>
        </div>
        <div class="card-footer ciop-detail-footer">
            <a href="{{ route('teams.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <div class="ciop-detail-footer-spacer"></div>
            <form action="{{ route('teams.destroy', $team->id) }}" method="POST" class="d-inline"
                onsubmit="return confirm('Excluir esta equipe?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="far fa-trash-alt"></i> Excluir
                </button>
            </form>
        </div>
    </div>
@endsection
