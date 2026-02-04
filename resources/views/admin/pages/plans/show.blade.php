@extends('adminlte::page')

@section('title', "Detalhes Plano { $plan->name }")

@section('content_header')
    <h1 class="m-0 text-dark">Detalhes Plano <b>{{ $plan->name }}</b></h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @csrf
            <div class="row">
                <ul>
                    <li><b>Nome:</b> {{ $plan->name }}</li>
                    <li><b>Preço:</b> R$ {{ number_format($plan->price, 2, ',', '.') }}</li>
                    <li><b>Url:</b> {{ $plan->url }}</li>
                    <li><b>Descrição:</b> {{ $plan->description }}</li>
                </ul>
            </div>
            <!--row-->
            @include('admin.includes.alerts')

            <form action="{{ route('plans.destroy', $plan->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="far fa-trash-alt"></i>
                    <span class=m-4>Excluir</span>
                </button>
            </form>
        </div>
        <!--card-body-->
    </div>
    <!--card-->
@endsection
