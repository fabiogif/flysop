@extends('adminlte::page')

@section('title', 'Adicionar Ocorrência')

@section('content_header')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Painel de Controle</a></li>
        <li class="breadcrumb-item"><a href="{{ route('occurrences.index') }}">Ocorrências</a></li>
        <li class="breadcrumb-item active">Adicionar</li>
    </ol>
    <h1 class="m-0 text-dark">Adicionar Ocorrência</h1>
@stop

@section('content')
    <form action="{{ route('occurrences.store') }}" class="form" method="POST" enctype="multipart/form-data">
        @csrf
        @include('admin.pages.occurrences._partials.form')
    </form>
@endsection
