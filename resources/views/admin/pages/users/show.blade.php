@extends('adminlte::page')

@section('title', 'Detalhes do Usuário')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => $user->name,
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Usuários', 'url' => route('users.index')],
            ['label' => $user->name],
        ],
        'actionsHtml' => '<a href="'.route('users.edit', $user->id).'" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Editar</a>',
    ])
@stop

@section('content')
    @include('admin.includes.alerts')

    <div class="card">
        <div class="card-body">
            <dl class="ciop-detail-grid">
                <div class="ciop-detail-item">
                    <dt>Nome</dt>
                    <dd>{{ $user->name }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>E-mail</dt>
                    <dd>{{ $user->email }}</dd>
                </div>
            </dl>
        </div>
        <div class="card-footer ciop-detail-footer">
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <div class="ciop-detail-footer-spacer"></div>
            @if ((int) $user->id !== (int) auth()->id())
                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline"
                    onsubmit="return confirm('Remover este membro da organização?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="far fa-trash-alt"></i> Excluir
                    </button>
                </form>
            @endif
        </div>
    </div>
@endsection
