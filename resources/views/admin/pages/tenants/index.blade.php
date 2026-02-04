@extends('adminlte::page')
@section('title', 'Empresa')

@section('content_header')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Painel de Controle</a> </li>
    <li class="breadcrumb-item active"><a href="{{ route('tenants.index') }}">Empresa</a> </li>
</ol>

<h1 class="m-0 text-dark">Empresa
    <a href="{{ route('tenants.create') }}" class="btn btn-primary mr-5">
        <i class="fas fa-save"></i>
        <span class=m-4>Adicionar</span>
    </a>
</h1>
@stop

@section('content')
<div class="card">
    @include('admin.includes.alerts')

    <div class="card-header">
        <form action="{{ route('tenants.search') }}" method="POST" class="form form-inline">
            @csrf
            <div class="form-group">
                <input type="text" class="form-control mr-2" name="filter" placeholder="Nome ou CNPJ" value="{{ $filters['filter'] ?? '' }}">
                <button type="submit" class="btn btn-info">
                    <i class="fas fa-search"></i>
                    <span class=m-4>Pesquisar</span>
                </button>
            </div>
        </form>
    </div>


    <div class="card-body">
        <table class="table table-condensed">
            <thead>
                <tr>
                    <th style="max-width:150px">Logo</th>
                    <th>Nome</th>
                    <th>Url</th>
                    <th>CNPJ</th>
                    <th>E-mail</th>
                    <th>Status</th>
                    <th width="150px">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tenants as $tenant)
                <tr>
                    <td></td>
                    <td>{{ $tenant->name }}</td>
                    <td>{{ $tenant->url }}</td>
                    <td>{{ $tenant->cnpj }}</td>
                    <td>{{ $tenant->email }}</td>
                    <td>{{ $tenant->active == '1' ? 'Ativo' : 'Inativo' }}</td>
                    <td style=" width: 10px">
                        <a href="{{ route('tenants.edit', $tenant->id) }}" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                        <a href="{{ route('tenants.show', $tenant->id) }}" class="btn btn-info"><i class="fas fa-search"></i></a>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        @if (isset($filters))
        {!! $tenants->appends($filters)->links() !!}
        @else
        {!! $tenants->links() !!}
        @endif
    </div>
</div>
@stop