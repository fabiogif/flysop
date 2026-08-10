@extends('adminlte::page')
@section('title', 'Organização')

@section('content_header')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Painel</a></li>
        <li class="breadcrumb-item active">Organização</li>
    </ol>
    <h1 class="m-0 text-dark">Configurações da organização</h1>
@stop

@section('content')
    @include('admin.includes.alerts')

    <div class="card">
        <div class="card-header"><h3 class="card-title">Dados da conta</h3></div>
        <div class="card-body">
            <form method="POST" action="{{ route('settings.organisation.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Nome</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $tenant->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>E-mail</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email', $tenant->email) }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>CNPJ</label>
                    <input type="text" name="cnpj" class="form-control" value="{{ old('cnpj', $tenant->cnpj) }}">
                </div>
                <div class="form-group">
                    <label>Domínio / URL</label>
                    <input type="text" name="url" class="form-control" value="{{ old('url', $tenant->url) }}"
                        placeholder="ex.: prefeitura.exemplo.gov.br">
                    <small class="form-text text-muted">Identificador ou domínio da organização (SSO não integrado).</small>
                </div>
                <div class="form-group">
                    <label>Logo</label>
                    @if ($tenant->logo)
                        <div class="mb-2"><img src="{{ url('storage/' . $tenant->logo) }}" alt="Logo" height="48"></div>
                    @endif
                    <input type="file" name="logo" class="form-control-file" accept="image/*">
                </div>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Plano e assinatura</h3></div>
        <div class="card-body">
            <p class="text-muted mb-3">
                Gestão de cobrança (seats, faturas, portal de pagamento) não está integrada.
                Os dados abaixo são metadados manuais da organização.
            </p>
            <dl class="row mb-0">
                <dt class="col-sm-3">Assinatura</dt>
                <dd class="col-sm-9">{{ $tenant->subscription ?: '—' }}</dd>
                <dt class="col-sm-3">ID</dt>
                <dd class="col-sm-9">{{ $tenant->subscription_id ?: '—' }}</dd>
                <dt class="col-sm-3">Expira em</dt>
                <dd class="col-sm-9">{{ $tenant->expires_at ?: '—' }}</dd>
                <dt class="col-sm-3">Ativa</dt>
                <dd class="col-sm-9">{{ $tenant->subscription_active ? 'Sim' : 'Não' }}</dd>
                <dt class="col-sm-3">Suspensa</dt>
                <dd class="col-sm-9">{{ $tenant->subscription_suspended ? 'Sim' : 'Não' }}</dd>
            </dl>
        </div>
    </div>

    <div class="card border-danger">
        <div class="card-header bg-danger">
            <h3 class="card-title text-white mb-0">Zona de perigo</h3>
        </div>
        <div class="card-body">
            <p class="mb-3">
                Excluir a organização remove permanentemente dados associados a este workspace.
                Esta ação <strong>não pode ser desfeita</strong>.
            </p>
            <form method="POST" action="{{ route('settings.organisation.destroy') }}"
                onsubmit="return confirm('Confirma a exclusão definitiva desta organização?');">
                @csrf
                @method('DELETE')
                <div class="form-group">
                    <label>
                        Digite <code>{{ $tenant->name }}</code> para confirmar
                    </label>
                    <input type="text" name="confirmation" class="form-control @error('confirmation') is-invalid @enderror"
                        autocomplete="off" required>
                    @error('confirmation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-outline-danger">
                    <i class="fas fa-trash"></i> Excluir organização
                </button>
            </form>
        </div>
    </div>
@stop
