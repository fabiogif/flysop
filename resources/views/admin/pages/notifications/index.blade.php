@extends('adminlte::page')
@section('title', 'Notificações')

@section('content_header')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Painel de Controle</a> </li>
        <li class="breadcrumb-item active"><a href="{{ route('notifications.index') }}">Notificações</a> </li>
    </ol>

    <h1 class="m-0 text-dark">Notificações
        <form action="{{ route('notifications.read-all') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-secondary mr-5">
                <i class="fas fa-check-double"></i>
                <span class=m-4>Marcar todas como lidas</span>
            </button>
        </form>
    </h1>
@stop

@section('content')
    <div class="card">
        @include('admin.includes.alerts')

        <div class="card-body p-0">
            <table class="table table-condensed mb-0">
                <thead>
                    <tr>
                        <th></th>
                        <th>Mensagem</th>
                        <th>Quando</th>
                        <th width="120px">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($notifications as $notification)
                        <tr class="{{ $notification->read_at ? '' : 'font-weight-bold' }}">
                            <td>
                                @if (! $notification->read_at)
                                    <span class="badge badge-primary">Nova</span>
                                @endif
                            </td>
                            <td>{{ $notification->data['message'] ?? '—' }}</td>
                            <td>{{ $notification->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-info btn-sm">
                                        {{ $notification->read_at ? 'Abrir' : 'Marcar como lida' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">Nenhuma notificação.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {!! $notifications->links() !!}
        </div>
    </div>
@stop
