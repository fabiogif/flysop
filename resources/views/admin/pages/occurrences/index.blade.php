@extends('adminlte::page')
@section('title', 'Ocorrências')

@section('content_header')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Painel de Controle</a> </li>
        <li class="breadcrumb-item active"><a href="{{ route('occurrences.index') }}">Ocorrência</a> </li>
    </ol>

    <h1 class="m-0 text-dark">Ocorrência
        <a href="{{ route('occurrences.create') }}" class="btn btn-primary mr-5">
            <i class="fas fa-save"></i>
            <span class=m-4>Adicionar</span>
        </a>
    </h1>
@stop

@section('content')
    <div class="card">
        @include('admin.includes.alerts')

        <div class="card-header">
            <form action="{{ route('occurrences.search') }}" method="POST" class="form form-inline">
                @csrf
                <div class="form-group">
                    <input type="text" class="form-control mr-2" name="filter" placeholder="Titulo"
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
                        <th>Protocolo</th>
                        <th>Nome</th>
                        <th>Título</th>
                        <th>Tipo</th>
                        <th>Prioridade</th>
                        <th>Órgão</th>
                        <th>Status</th>
                        <th>E-mail</th>
                        <th>Última atualização</th>
                        <th width="200px">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($occurrences as $occurrence)
                        <tr>
                            <td>{{ $occurrence->protocol ?? '—' }}</td>
                            <td>{{ $occurrence->name }}</td>
                            <td>{{ $occurrence->title }}</td>
                            <td>{{ $occurrence->nameType ?? '—' }}</td>
                            <td>
                                @if ($occurrence->priority)
                                    <span class="badge" style="background-color: {{ $occurrence->priority->color ?? '#6c757d' }}; color: #fff;">{{ $occurrence->priority->name }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $occurrence->nameIssuings ?? '—' }}</td>
                            <td>
                                @php
                                    $statusName = $occurrence->nameStatus ?? '—';
                                    $badgeClass = match(strtolower($statusName)) {
                                        'aberta', 'aberto' => 'badge-warning',
                                        'em atendimento', 'em andamento' => 'badge-info',
                                        'finalizada', 'concluída', 'resolvida' => 'badge-success',
                                        'cancelada' => 'badge-secondary',
                                        default => 'badge-light text-dark',
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $statusName }}</span>
                            </td>
                            <td>{{ $occurrence->email }}</td>
                            <td>{{ $occurrence->updated_at ? $occurrence->updated_at->format('d/m/Y H:i') : '—' }}</td>
                            <td style="width: 10px">
                                <a href="{{ route('occurrences.edit', $occurrence->id) }}" class="btn btn-warning btn-sm" title="Editar"><i class="fas fa-edit"></i></a>
                                <a href="{{ route('occurrences.show', $occurrence->id) }}" class="btn btn-info btn-sm" title="Ver detalhes"><i class="fas fa-search"></i></a>
                                @if (auth()->user()->isAdmin() || auth()->user()->driver)
                                    <a href="{{ route('driver.dashboard') }}" class="btn btn-secondary btn-sm" title="Painel do motorista"><i class="fas fa-truck"></i></a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    @if (count($occurrences) == 0)
                        <td>Não existe informações</td>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            @if (isset($filters))
                {!! $occurrences->appends($filters)->links() !!}
            @else
                {!! $occurrences->links() !!}
            @endif
        </div>
    </div>
@stop
