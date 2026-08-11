@extends('adminlte::page')
@section('title', 'Usuários')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => 'Usuários',
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Usuários'],
        ],
        'actionsHtml' => '<a href="'.route('users.invite.create').'" class="btn btn-success btn-sm"><i class="fas fa-envelope"></i> Convidar</a> <a href="'.route('users.create').'" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Adicionar</a>',
    ])
@stop

@section('content')
    <div class="card">
        @include('admin.includes.alerts')

        @include('admin.includes.search-toolbar', [
            'action' => route('users.search'),
            'placeholder' => 'Nome e e-mail',
            'filters' => $filters ?? [],
        ])

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover ciop-table mb-0">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <div class="ciop-actions">
                                        <a href="{{ route('users.show', $user->id) }}" class="btn btn-outline-info btn-sm" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-outline-warning btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('users.roles', $user->id) }}" class="btn btn-outline-secondary btn-sm" title="Cargos">
                                            <i class="fas fa-address-card"></i>
                                        </a>
                                        @if ((int) $user->id !== (int) auth()->id())
                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline"
                                                onsubmit="return confirm('Remover este membro da organização?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Remover">
                                                    <i class="fas fa-user-minus"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="ciop-empty">Nenhum registro encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            @if (isset($filters))
                {!! $users->appends($filters)->links() !!}
            @else
                {!! $users->links() !!}
            @endif
        </div>
    </div>
@endsection
