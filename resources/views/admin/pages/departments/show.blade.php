@extends('adminlte::page')

@section('title', 'Detalhes do Departamento')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => $department->name,
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Departamentos', 'url' => route('departments.index')],
            ['label' => $department->name],
        ],
        'actionsHtml' => '<a href="'.route('departments.edit', $department->id).'" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Editar</a>',
    ])
@stop

@section('content')
    @include('admin.includes.alerts')

    <div class="card">
        <div class="card-body">
            <dl class="ciop-detail-grid">
                <div class="ciop-detail-item">
                    <dt>Nome</dt>
                    <dd>{{ $department->name }}</dd>
                </div>
                <div class="ciop-detail-item ciop-detail-wide">
                    <dt>Equipes</dt>
                    <dd>
                        @forelse ($department->teams as $team)
                            <span class="badge badge-secondary">{{ $team->name }}</span>
                        @empty
                            —
                        @endforelse
                    </dd>
                </div>
            </dl>
        </div>
        <div class="card-footer ciop-detail-footer">
            <a href="{{ route('departments.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <div class="ciop-detail-footer-spacer"></div>
            <form action="{{ route('departments.destroy', $department->id) }}" method="POST" class="d-inline"
                onsubmit="return confirm('Excluir este departamento?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="far fa-trash-alt"></i> Excluir
                </button>
            </form>
        </div>
    </div>
@endsection
