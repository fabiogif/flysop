@extends('adminlte::page')

@section('title', "Detalhes do Detalhe{{ $plan->name }}")

@section('content_header')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Painel de Controle</a> </li>
        <li class="breadcrumb-item active"><a href="{{ route('plans.index') }}">Planos</a> </li>
        <li class="breadcrumb-item active"><a href="{{ route('plans.show', $plan->url) }}">{{ $plan->name }}</a> </li>
        <li class="breadcrumb-item active">
            <a href="{{ route('details.plans.index', $plan->url) }}" class="active">Detalhes</a>
        </li>
        <li class="breadcrumb-item active">
            <a href="{{ route('details.plans.index', $plan->url) }}" class="active">Detalhes do Detalhe</a>
        </li>
    </ol>


    <h1 class="m-0 text-dark">Alterar Detalhe - <b> {{ $details->name }} </b></h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <ul>
                <li><b>Nome:</b> {{ $details->name }}</li>
            </ul>
            <div class="card-footer">
                <form action="{{ route('details.plans.delete', [$plan->id, $details->id]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="far fa-trash-alt"></i>
                        <span class=m-4>Excluir</span>
                    </button>
                </form>
            </div>
        </div>
        <!--card-header-->
    </div>
@endsection()
