@extends('adminlte::page')
@section('title', 'Detalhes do plano')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => 'Detalhes do plano - '.$plan->name,
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Planos', 'url' => route('plans.index')],
            ['label' => $plan->name, 'url' => route('plans.show', $plan->url)],
            ['label' => 'Detalhes'],
        ],
        'actionsHtml' => '<a href="'.route('details.plans.create', $plan->id).'" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Adicionar</a>',
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
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($details as $detail)
                            <tr>
                                <td>{{ $detail->name }}</td>
                                <td>
                                    <div class="ciop-actions">
                                        <a href="{{ route('details.plans.show', [$plan->id, $detail->id]) }}" class="btn btn-outline-info btn-sm" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('details.plans.edit', [$plan->id, $detail->id]) }}" class="btn btn-outline-warning btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
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
                {!! $details->appends($filters)->links() !!}
            @else
                {!! $details->links() !!}
            @endif
        </div>
    </div>
@endsection
