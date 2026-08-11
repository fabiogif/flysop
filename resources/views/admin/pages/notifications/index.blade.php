@extends('adminlte::page')
@section('title', 'Notificações')

@section('content_header')
    @php
        ob_start();
    @endphp
    <form action="{{ route('notifications.read-all') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-secondary btn-sm">
            <i class="fas fa-check-double"></i> Marcar todas como lidas
        </button>
    </form>
    @php
        $actionsHtml = ob_get_clean();
    @endphp

    @include('admin.includes.page-header', [
        'title' => 'Notificações',
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Notificações'],
        ],
        'actionsHtml' => $actionsHtml,
    ])
@stop

@section('content')
    <div class="card">
        @include('admin.includes.alerts')

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover ciop-table mb-0">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Mensagem</th>
                            <th>Quando</th>
                            <th>Ação</th>
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
                                    <div class="ciop-actions">
                                        <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-info btn-sm">
                                                {{ $notification->read_at ? 'Abrir' : 'Marcar como lida' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="ciop-empty">Nenhuma notificação.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {!! $notifications->links() !!}
        </div>
    </div>
@endsection
