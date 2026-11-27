@extends('adminlte::page')
@section('title', 'Informativos')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => 'Informativos',
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Informativos'],
        ],
        'actionsHtml' => '<a href="'.route('announcements.create').'" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Adicionar</a>',
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
                            <th>Título</th>
                            <th>Órgão</th>
                            <th>Status</th>
                            <th>Publicado em</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($announcements as $announcement)
                            <tr>
                                <td>{{ $announcement->title }}</td>
                                <td>{{ $announcement->issuing->name ?? '—' }}</td>
                                <td>
                                    @if ($announcement->is_published)
                                        <span class="badge badge-success">Publicado</span>
                                    @else
                                        <span class="badge badge-secondary">Rascunho</span>
                                    @endif
                                </td>
                                <td>{{ $announcement->published_at ? $announcement->published_at->format('d/m/Y H:i') : '—' }}</td>
                                <td>
                                    <div class="ciop-actions">
                                        <a href="{{ route('announcements.edit', $announcement->id) }}" class="btn btn-outline-warning btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('announcements.destroy', $announcement->id) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('Excluir este informativo?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Excluir">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="ciop-empty">Nenhum informativo cadastrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {!! $announcements->links() !!}
        </div>
    </div>
@endsection
