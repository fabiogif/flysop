@extends('adminlte::page')
@section('title', 'Notificações')

@section('content_header')
    @php
        ob_start();
    @endphp
    <form action="{{ route('notifications.read-all') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-sm ciop-btn-panel" {{ $notifications->total() === 0 ? 'disabled' : '' }}>
            <i class="fas fa-check-double" aria-hidden="true"></i> Marcar todas como lidas
        </button>
    </form>
    @php
        $actionsHtml = ob_get_clean();
        $unreadCount = auth()->user()->unreadNotifications()->count();
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
    @include('admin.includes.alerts')

    <div class="ciop-notif-stats">
        <article class="ciop-notif-stat {{ $unreadCount > 0 ? 'is-accent' : '' }}">
            <span class="ciop-notif-stat-icon" aria-hidden="true"><i class="fas fa-bell"></i></span>
            <div>
                <p class="ciop-notif-stat-label">Não lidas</p>
                <p class="ciop-notif-stat-value">{{ $unreadCount }}</p>
            </div>
        </article>
        <article class="ciop-notif-stat">
            <span class="ciop-notif-stat-icon" aria-hidden="true"><i class="fas fa-inbox"></i></span>
            <div>
                <p class="ciop-notif-stat-label">Total nesta página</p>
                <p class="ciop-notif-stat-value">{{ $notifications->count() }}</p>
            </div>
        </article>
        <article class="ciop-notif-stat">
            <span class="ciop-notif-stat-icon" aria-hidden="true"><i class="fas fa-layer-group"></i></span>
            <div>
                <p class="ciop-notif-stat-label">Todas</p>
                <p class="ciop-notif-stat-value">{{ $notifications->total() }}</p>
            </div>
        </article>
    </div>

    <div class="ciop-notif-list">
        @forelse ($notifications as $notification)
            @php
                $isUnread = is_null($notification->read_at);
                $type = class_basename($notification->type);
                $icon = match (true) {
                    str_contains($type, 'Sla') => 'fa-exclamation-triangle',
                    str_contains($type, 'Assigned') => 'fa-user-check',
                    str_contains($type, 'Status') => 'fa-exchange-alt',
                    str_contains($type, 'Invitation') => 'fa-envelope-open-text',
                    str_contains($type, 'Report') => 'fa-file-alt',
                    default => 'fa-bell',
                };
                $tone = match (true) {
                    str_contains($type, 'Sla') => 'warn',
                    str_contains($type, 'Assigned') => 'accent',
                    str_contains($type, 'Status') => 'info',
                    default => 'teal',
                };
                $when = $notification->created_at->diffForHumans();
                $whenExact = $notification->created_at->format('d/m/Y H:i');
                $message = $notification->data['message'] ?? 'Notificação sem mensagem.';
                $protocol = $notification->data['protocol'] ?? null;
            @endphp

            <article class="ciop-notif-card tone-{{ $tone }} {{ $isUnread ? 'is-unread' : 'is-read' }}">
                <div class="ciop-notif-card-icon" aria-hidden="true">
                    <i class="fas {{ $icon }}"></i>
                </div>

                <div class="ciop-notif-card-body">
                    <div class="ciop-notif-card-top">
                        <div class="ciop-notif-card-meta">
                            @if ($isUnread)
                                <span class="ciop-notif-badge">Nova</span>
                            @endif
                            @if ($protocol)
                                <span class="ciop-notif-protocol">{{ $protocol }}</span>
                            @endif
                            <time datetime="{{ $notification->created_at->toIso8601String() }}" title="{{ $whenExact }}">
                                {{ $when }}
                            </time>
                        </div>
                        <p class="ciop-notif-card-time-exact d-none d-md-block">{{ $whenExact }}</p>
                    </div>

                    <p class="ciop-notif-card-message">{{ $message }}</p>

                    <div class="ciop-notif-card-actions">
                        <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm {{ $isUnread ? 'ciop-btn-panel' : 'btn-outline-secondary' }}">
                                <i class="fas {{ $isUnread ? 'fa-check' : 'fa-external-link-alt' }}" aria-hidden="true"></i>
                                {{ $isUnread ? 'Marcar como lida' : 'Abrir' }}
                            </button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="ciop-notif-empty">
                <span class="ciop-notif-empty-icon" aria-hidden="true"><i class="fas fa-bell-slash"></i></span>
                <h2>Nenhuma notificação</h2>
                <p>Quando houver atribuições, mudanças de status ou alertas de SLA, elas aparecem aqui.</p>
            </div>
        @endforelse
    </div>

    @if ($notifications->hasPages())
        <div class="ciop-notif-pager">
            {!! $notifications->links() !!}
        </div>
    @endif
@endsection
