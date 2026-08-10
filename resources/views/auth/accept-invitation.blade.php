@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('auth_header', 'Aceitar convite')

@section('auth_body')
    <p class="login-box-msg">
        Olá, <strong>{{ $invitation->name }}</strong>. Defina sua senha para entrar em
        <strong>{{ $invitation->tenant?->name }}</strong>.
    </p>

    <form method="POST" action="{{ route('invitations.accept.store', $invitation->token) }}">
        @csrf
        <div class="input-group mb-3">
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                placeholder="Senha" required>
            <div class="input-group-append">
                <div class="input-group-text"><span class="fas fa-lock"></span></div>
            </div>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
        <div class="input-group mb-3">
            <input type="password" name="password_confirmation" class="form-control"
                placeholder="Confirmar senha" required>
            <div class="input-group-append">
                <div class="input-group-text"><span class="fas fa-lock"></span></div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Criar conta</button>
    </form>
@stop
