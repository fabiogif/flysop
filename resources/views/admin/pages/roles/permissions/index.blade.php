@extends('adminlte::page')
@section('title', 'Permissões do Cargo')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => 'Permissões do Cargo - '.$role->name,
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Cargos', 'url' => route('roles.index')],
            ['label' => 'Permissões do Cargo'],
        ],
        'actionsHtml' => '<a href="'.route('roles.permissions.available', $role->id).'" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Adicionar</a>',
    ])
@stop

@section('content')
    <div class="card">
        @include('admin.includes.alerts')

        @include('admin.includes.search-toolbar', [
            'action' => route('roles.search'),
            'placeholder' => 'Nome',
            'filters' => $filters ?? [],
        ])

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover ciop-table mb-0">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($permissions as $permission)
                            <tr>
                                <td>{{ $permission->name }}</td>
                                <td>
                                    <div class="ciop-actions">
                                        <a href="{{ route('roles.permissions.detach', [$role->id, $permission->id]) }}"
                                            class="btn btn-outline-danger btn-sm" title="Desvincular"
                                            onclick="return confirm('Desvincular esta permissão?');">
                                            <i class="fas fa-unlink"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="ciop-empty">Nenhum registro encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            @if (isset($filters))
                {!! $permissions->appends($filters)->links() !!}
            @else
                {!! $permissions->links() !!}
            @endif
        </div>
    </div>
@endsection
