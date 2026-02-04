@extends('adminlte::page')

@section('title', "Detalhes do plano - $plan->name ")

@section('content_header')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Painel de Controle</a> </li>
        <li class="breadcrumb-item active"><a href="{{ route('plans.index') }}">Planos</a> </li>
        <li class="breadcrumb-item active"><a href="{{ route('plans.show', $plan->url) }}">{{ $plan->name }}</a> </li>
        <li class="breadcrumb-item active">
            <a href="{{ route('details.plans.index', $plan->url) }}" class="active">Detalhes</a>
        </li>
    </ol>

    <h1 class="m-0 text-dark">Detalhes do plano - <b>{{ $plan->name }}</b> <a
            href="{{ route('details.plans.create', $plan->id) }}" class="btn btn-primary mr-5"><i
                class="fas fa-save"></i><span class="m-4">Adicionar</span></a>
    </h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <form action="{{ route('plans.search') }}" method="POST" class="form form-inline">
                @csrf
                <div class="form-group">
                    <input type="text" class="form-control mr-2" name="filter" placeholder="Nome ou Preço"
                        value="{{ $filters['filter'] ?? '' }}">
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-search"></i>
                        <span class=m-4>Pesquisar</span>
                    </button>
                </div>
            </form>
        </div>


        <div class="card-body">
            @include('admin.includes.alerts')
            <table class="table table-condensed">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th width="200px">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($details as $detail)
                        <tr>
                            <td>{{ $detail->name }}</td>
                            <td style="width: 10px">
                                <a href="{{ route('details.plans.edit', [$plan->id, $detail->id]) }}"
                                    class="btn btn-warning">Alterar</a>
                                <a href="{{ route('details.plans.show', [$plan->id, $detail->id]) }}"
                                    class="btn btn-info">Visualizar</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            @if (isset($filters))
                {!! $details->appends($filters)->links() !!}
            @else
                {!! $details->links() !!}
            @endif
        </div>
    </div>
@stop
