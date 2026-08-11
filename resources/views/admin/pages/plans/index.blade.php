@extends('adminlte::page')
@section('title', 'Planos')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => 'Planos',
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Planos'],
        ],
        'actionsHtml' => '<a href="'.route('plans.create').'" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Adicionar</a>',
    ])
@stop

@section('content')
    <div class="card">
        @include('admin.includes.alerts')

        @include('admin.includes.search-toolbar', [
            'action' => route('plans.search'),
            'placeholder' => 'Nome ou Preço',
            'filters' => $filters ?? [],
        ])

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover ciop-table mb-0">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Preço</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($plans as $plan)
                            <tr>
                                <td>{{ $plan->name }}</td>
                                <td>R$ {{ number_format($plan->price, 2, ',', '.') }}</td>
                                <td>
                                    <div class="ciop-actions">
                                        <a href="{{ route('plans.show', $plan->id) }}" class="btn btn-outline-info btn-sm" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('plans.edit', $plan->id) }}" class="btn btn-outline-warning btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('details.plans.index', $plan->id) }}" class="btn btn-outline-secondary btn-sm" title="Detalhes">
                                            <i class="fas fa-list"></i>
                                        </a>
                                        <a href="{{ route('plans.profiles', $plan->id) }}" class="btn btn-outline-secondary btn-sm" title="Vincular">
                                            <i class="fas fa-link"></i>
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
                {!! $plans->appends($filters)->links() !!}
            @else
                {!! $plans->links() !!}
            @endif
        </div>
    </div>
@endsection
