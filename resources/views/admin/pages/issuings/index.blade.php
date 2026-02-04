@extends('adminlte::page')
@section('title', 'Orgão')

@section('content_header')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Painel de Controle</a> </li>
        <li class="breadcrumb-item active"><a href="{{ route('issuings.index') }}">Orgão</a> </li>
    </ol>

    <h1 class="m-0 text-dark">Orgão
        <a href="{{ route('issuings.create') }}" class="btn btn-primary mr-5">
            <i class="fas fa-save"></i>
            <span class=m-4>Adicionar</span>
        </a>
    </h1>
@stop

@section('content')
    <div class="card">
        @include('admin.includes.alerts')
        <div class="card-header">
            <form action="{{ route('issuings.search') }}" method="POST" class="form form-inline">
                @csrf
                <div class="form-group">
                    <input type="text" class="form-control mr-2" name="filter" placeholder="Nome"
                        value="{{ $filters['filter'] ?? '' }}">
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-search"></i>
                        <span class=m-4>Pesquisar</span>
                    </button>
                </div>
            </form>
        </div>

        <div class="card-body">
            <table class="table table-condensed">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th width="250px">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($issuings as $issuing)
                        <tr>
                            <td>{{ $issuing->name }}</td>
                            <td style="width: 10px">
                                <a href="{{ route('issuings.edit', $issuing->id) }}" class="btn btn-warning"><i
                                        class="fas fa-edit"></i></a>
                                <a href="{{ route('issuings.show', $issuing->id) }}" class="btn btn-info"><i
                                        class="fas fa-search"></i></a>
                                </a>

                            </td>
                        </tr>
                    @endforeach
                    @if (count($issuings) == 0)
                        <td>Não existe informações</td>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            @if (isset($filters))
                {!! $issuings->appends($filters)->links() !!}
            @else
                {!! $issuings->links() !!}
            @endif
        </div>
    </div>
@stop
