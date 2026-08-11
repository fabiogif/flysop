@extends('adminlte::page')
@section('title', 'Permissões')

@section('content_header')
    @php
        ob_start();
    @endphp
    @can('update', Mode::class)
        <a href="{{ route('permission.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Adicionar
        </a>
    @endcan
    @php
        $actionsHtml = ob_get_clean();
    @endphp

    @include('admin.includes.page-header', [
        'title' => 'Permissões',
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Permissões'],
        ],
        'actionsHtml' => $actionsHtml,
    ])
@stop

@section('content')
    <div class="card">
        @include('admin.includes.alerts')

        @include('admin.includes.search-toolbar', [
            'action' => route('permission.search'),
            'placeholder' => 'Nome',
            'filters' => $filters ?? [],
        ])

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover ciop-table mb-0">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Descrição</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($permissions as $permission)
                            <tr>
                                <td>{{ $permission->name }}</td>
                                <td>{{ $permission->description }}</td>
                                <td>
                                    <div class="ciop-actions">
                                        <a href="{{ route('permission.show', $permission->id) }}" class="btn btn-outline-info btn-sm" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('permission.edit', $permission->id) }}" class="btn btn-outline-warning btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('permission.profiles', $permission->id) }}" class="btn btn-outline-secondary btn-sm" title="Perfis">
                                            <i class="fas fa-address-book"></i>
                                        </a>
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
                {!! $permissions->appends($filters)->links() !!}
            @else
                {!! $permissions->links() !!}
            @endif
        </div>
    </div>
@endsection
