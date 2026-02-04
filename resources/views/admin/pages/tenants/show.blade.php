@extends('adminlte::page')

@section('title', "Detalhes Empresa - $tenant->name")

@section('content_header')
    <h1 class="m-0 text-dark">Detalhes Empresa <b>{{ $tenant->name }}</b></h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @csrf
            <div class="row">
                <ul>
                    <li>
                        <img src="{{ Storage::disk('s3')->url("{$tenant->logo}") }}" alt="{{ $tenant->name }}"
                            style="max-width:150px" />
                    </li>
                    <li><b>Nome:</b> {{ $tenant->name }}</li>
                    <li><b>URL:</b> {{ $tenant->url }}</li>
                    <li><b>CNPJ:</b> {{ $tenant->cnpj }}</li>
                    <li><b>E-mail:</b> {{ $tenant->email }}</li>
                </ul>

            </div>
            <hr>
            <h3>Assinatura</h3>
            <div class="row">

                <ul>
                    <li><b>Data da Assinatura:</b> {{ $tenant->subscription }}</li>
                    <li><b>Data da Expiração:</b> {{ $tenant->expires_at }}</li>
                    <li><b>Indentificador:</b> {{ $tenant->subscription_id }}</li>
                    <li><b>Status</b> {{ $tenant->active == '1' ? 'Ativo' : 'Inativo' }}</li>
                </ul>
            </div>
            <!--row-->
            @include('admin.includes.alerts')

        </div>
        <!--card-body-->
    </div>
    <!--card-->
@endsection
