@extends('adminlte::page')

@section('title', 'Detalhes da Empresa')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => $tenant->name,
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Empresa', 'url' => route('tenants.index')],
            ['label' => $tenant->name],
        ],
        'actionsHtml' => '<a href="'.route('tenants.edit', $tenant->id).'" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Editar</a>',
    ])
@stop

@section('content')
    @include('admin.includes.alerts')

    <div class="card">
        <div class="card-body">
            <dl class="ciop-detail-grid">
                @if ($tenant->logo)
                    <div class="ciop-detail-item ciop-detail-wide">
                        <dt>Logo</dt>
                        <dd>
                            <img src="{{ Storage::disk('s3')->url($tenant->logo) }}" alt="{{ $tenant->name }}"
                                style="max-width: 150px;">
                        </dd>
                    </div>
                @endif
                <div class="ciop-detail-item">
                    <dt>Nome</dt>
                    <dd>{{ $tenant->name }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>URL</dt>
                    <dd>{{ $tenant->url ?? '—' }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>CNPJ</dt>
                    <dd>{{ $tenant->cnpj ?? '—' }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>E-mail</dt>
                    <dd>{{ $tenant->email ?? '—' }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>Status</dt>
                    <dd>{{ $tenant->active == '1' ? 'Ativo' : 'Inativo' }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>Data da assinatura</dt>
                    <dd>{{ $tenant->subscription ?? '—' }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>Data da expiração</dt>
                    <dd>{{ $tenant->expires_at ?? '—' }}</dd>
                </div>
                <div class="ciop-detail-item">
                    <dt>Identificador</dt>
                    <dd>{{ $tenant->subscription_id ?? '—' }}</dd>
                </div>
            </dl>
        </div>
        <div class="card-footer ciop-detail-footer">
            <a href="{{ route('tenants.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <div class="ciop-detail-footer-spacer"></div>
        </div>
    </div>
@endsection
