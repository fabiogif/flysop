@extends('adminlte::page')
@section('title', 'Adicionar Motorista')

@section('content_header')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Painel de Controle</a></li>
        <li class="breadcrumb-item"><a href="{{ route('drivers.index') }}">Motoristas</a></li>
        <li class="breadcrumb-item active">Adicionar</li>
    </ol>
    <h1 class="m-0 text-dark">Adicionar Motorista</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('drivers.store') }}" method="POST">
                @csrf
                @include('admin.pages.drivers._partials.form')
            </form>
        </div>
    </div>
@stop
