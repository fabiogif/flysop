@extends('adminlte::page')

@section('title', 'Alterar Ocorrência')

@section('content_header')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Painel de Controle</a></li>
        <li class="breadcrumb-item"><a href="{{ route('occurrences.index') }}">Ocorrências</a></li>
        <li class="breadcrumb-item active">Alterar</li>
    </ol>
    <h1 class="m-0 text-dark">
        Alterar Ocorrência
        @if (!empty($occurrences->protocol))
            <small class="text-muted font-weight-normal">{{ $occurrences->protocol }}</small>
        @endif
    </h1>
@stop

@section('content')
    <form action="{{ route('occurrences.update', $occurrences->id) }}" class="form" method="POST"
        enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.pages.occurrences._partials.form')
    </form>
@endsection
