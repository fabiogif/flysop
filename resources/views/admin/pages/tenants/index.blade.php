@extends('adminlte::page')
@section('title', 'Empresa')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => 'Empresa',
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Empresa'],
        ],
        'actionsHtml' => '<a href="'.route('tenants.create').'" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Adicionar</a>',
    ])
@stop

@section('content')
    <div class="card">
        @include('admin.includes.alerts')

        @include('admin.includes.search-toolbar', [
            'action' => route('tenants.search'),
            'placeholder' => 'Nome ou CNPJ',
            'filters' => $filters ?? [],
        ])

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover ciop-table mb-0">
                    <thead>
                        <tr>
                            <th>Logo</th>
                            <th>Nome</th>
                            <th>Url</th>
                            <th>CNPJ</th>
                            <th>E-mail</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tenants as $tenant)
                            <tr>
                                <td>
                                    @if ($tenant->logo)
                                        <img src="{{ Storage::disk('s3')->url($tenant->logo) }}" alt="{{ $tenant->name }}"
                                            style="max-height: 36px; max-width: 80px; object-fit: contain;">
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $tenant->name }}</td>
                                <td>{{ $tenant->url }}</td>
                                <td>{{ $tenant->cnpj }}</td>
                                <td>{{ $tenant->email }}</td>
                                <td>
                                    @if ($tenant->active == '1')
                                        <span class="badge badge-success">Ativo</span>
                                    @else
                                        <span class="badge badge-secondary">Inativo</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="ciop-actions">
                                        <a href="{{ route('tenants.show', $tenant->id) }}" class="btn btn-outline-info btn-sm" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('tenants.edit', $tenant->id) }}" class="btn btn-outline-warning btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="ciop-empty">Nenhum registro encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            @if (isset($filters))
                {!! $tenants->appends($filters)->links() !!}
            @else
                {!! $tenants->links() !!}
            @endif
        </div>
    </div>
@endsection
