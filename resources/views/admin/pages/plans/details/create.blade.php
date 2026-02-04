@extends('adminlte::page')

@section('title', "Adicionar Detalhe do plano {{ $plan->name }}")

@section('content_header')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Painel de Controle</a> </li>
        <li class="breadcrumb-item active"><a href="{{ route('plans.index') }}">Planos</a> </li>
        <li class="breadcrumb-item active"><a href="{{ route('plans.show', $plan->url) }}">{{ $plan->name }}</a> </li>
        <li class="breadcrumb-item active">
            <a href="{{ route('details.plans.index', $plan->url) }}" class="active">Detalhes</a>
        </li>
    </ol>

    <h1 class="m-0 text-dark">Adicionar Detalhe do plano - <b> {{ $plan->name }} </b></h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <form action="{{ route('details.plans.store', $plan->id) }}" method="POST">
                @method('POST')
                @include('admin.pages.plans.details._partials.form')
            </form>
        </div>
        <!--card-header-->
    </div>
@endsection()
