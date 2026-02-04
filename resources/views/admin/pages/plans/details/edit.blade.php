@extends('adminlte::page')

@section('title', "Alterar Detalhe {{ $plan->name }}")

@section('content_header')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Painel de Controle</a> </li>
        <li class="breadcrumb-item active"><a href="{{ route('plans.index') }}">Planos</a> </li>
        <li class="breadcrumb-item active"><a href="{{ route('plans.show', $plan->url) }}">{{ $plan->name }}</a> </li>
        <li class="breadcrumb-item active">
            <a href="{{ route('details.plans.index', $plan->url) }}" class="active">Detalhes</a>
        </li>
    </ol>

    <h1 class="m-0 text-dark">Alterar Detalhe - <b> {{ $details->name }} </b></h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <form action="{{ route('details.plans.update', [$plan->id, $details->id]) }}" method="POST">
                @method('PUT')
                @include('admin.pages.plans.details._partials.form')
            </form>
        </div>
        <!--card-header-->
    </div>
@endsection()
