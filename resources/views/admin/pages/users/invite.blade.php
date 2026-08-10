@extends('adminlte::page')
@section('title', 'Convidar usuário')

@section('content_header')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Painel</a></li>
        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Usuários</a></li>
        <li class="breadcrumb-item active">Convidar</li>
    </ol>
    <h1 class="m-0 text-dark">Convidar membro</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-5">
            <div class="card">
                @include('admin.includes.alerts')
                <div class="card-body">
                    <form method="POST" action="{{ route('users.invite.store') }}">
                        @csrf
                        <div class="form-group">
                            <label>Nome</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label>E-mail</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label>Cargo inicial (opcional)</label>
                            <select name="role_id" class="form-control">
                                <option value="">— Sem cargo —</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Enviar convite
                        </button>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">Voltar</a>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Convites pendentes</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Cargo</th>
                                <th>Expira</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($invitations as $invitation)
                                <tr>
                                    <td>{{ $invitation->name }}</td>
                                    <td>{{ $invitation->email }}</td>
                                    <td>{{ $invitation->role?->name ?? '—' }}</td>
                                    <td>
                                        {{ $invitation->expires_at->format('d/m/Y H:i') }}
                                        @if ($invitation->isExpired())
                                            <span class="badge badge-danger">Expirado</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('users.invite.destroy', $invitation->id) }}"
                                            onsubmit="return confirm('Cancelar este convite?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-muted text-center">Nenhum convite pendente.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $invitations->links() }}</div>
            </div>
        </div>
    </div>
@stop
