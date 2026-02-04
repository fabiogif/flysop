@extends('adminlte::page')
@section('title', 'Tipo de Ocorrência')

@section('content_header')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Painel de Controle</a> </li>
        <li class="breadcrumb-item active"><a href="{{ route('typeOccurrences.index') }}">Tipo de Ocorrência</a> </li>
    </ol>

    <h1 class="m-0 text-dark">Tipo de Ocorrência
        <a href="{{ route('typeOccurrences.create') }}" class="btn btn-primary mr-5">
            <i class="fas fa-save"></i>
            <span class=m-4>Adicionar</span>
        </a>
    </h1>
@stop

@section('content')
    <div class="card">
        @include('admin.includes.alerts')

        <div class="card-header">
            <form action="{{ route('typeOccurrences.search') }}" method="POST" class="form form-inline">
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
                    @foreach ($typeOccurrences as $typeOccurrence)
                        <tr>
                            <td>{{ $typeOccurrence->name }}</td>
                            <td style="width: 10px">
                                <a href="{{ route('typeOccurrences.edit', $typeOccurrence->id) }}"
                                    class="btn btn-warning"><i class="fas fa-edit"></i></a>
                                <a href="{{ route('typeOccurrences.show', $typeOccurrence->id) }}"
                                    class="btn btn-info"><i class="fas fa-search"></i></a>
                                </a>

                            </td>
                        </tr>
                    @endforeach
                    @if (count($typeOccurrences) == 0)
                        <td>Não existe informações</td>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            @if (isset($filters))
                {!! $typeOccurrences->appends($filters)->links() !!}
            @else
                {!! $typeOccurrences->links() !!}
            @endif
        </div>
    </div>
@stop
