@extends('adminlte::page')
@section('title', 'Editar Motorista')

@section('content_header')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Painel de Controle</a></li>
        <li class="breadcrumb-item"><a href="{{ route('drivers.index') }}">Motoristas</a></li>
        <li class="breadcrumb-item active">Editar</li>
    </ol>
    <h1 class="m-0 text-dark">Editar Motorista</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('drivers.update', $driver->id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('admin.pages.drivers._partials.form')
            </form>
        </div>
    </div>
@stop
