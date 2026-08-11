@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('adminlte_css_pre')
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@stop

@php($login_url = View::getSection('login_url') ?? config('adminlte.login_url', 'login'))
@php($register_url = View::getSection('register_url') ?? config('adminlte.register_url', 'register'))
@php($password_reset_url = View::getSection('password_reset_url') ?? config('adminlte.password_reset_url', 'password/reset'))

@if (config('adminlte.use_route_url', false))
    @php($login_url = $login_url ? route($login_url) : '')
    @php($register_url = $register_url ? route($register_url) : '')
    @php($password_reset_url = $password_reset_url ? route($password_reset_url) : '')
@else
    @php($login_url = $login_url ? url($login_url) : '')
    @php($register_url = $register_url ? url($register_url) : '')
    @php($password_reset_url = $password_reset_url ? url($password_reset_url) : '')
@endif

@section('auth_header', 'Entrar no painel')

@section('auth_body')
    <form action="{{ $login_url }}" method="post" novalidate>
        {{ csrf_field() }}

        <div class="ciop-auth-field">
            <label for="email">E-mail</label>
            <div class="ciop-auth-input">
                <input
                    id="email"
                    type="email"
                    name="email"
                    class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                    value="{{ old('email') }}"
                    placeholder="seu@email.gov.br"
                    autocomplete="username"
                    autofocus
                    required
                >
                <span class="ciop-auth-icon" aria-hidden="true">
                    <span class="fas fa-envelope {{ config('adminlte.classes_auth_icon', '') }}"></span>
                </span>
                @if ($errors->has('email'))
                    <div class="invalid-feedback d-block">
                        <strong>{{ $errors->first('email') }}</strong>
                    </div>
                @endif
            </div>
        </div>

        <div class="ciop-auth-field">
            <label for="password">Senha</label>
            <div class="ciop-auth-input has-toggle">
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                    placeholder="••••••••"
                    autocomplete="current-password"
                    required
                >
                <span class="ciop-auth-icon" aria-hidden="true">
                    <span class="fas fa-lock {{ config('adminlte.classes_auth_icon', '') }}"></span>
                </span>
                <button
                    type="button"
                    class="ciop-auth-toggle"
                    id="toggle-password"
                    title="Mostrar ou ocultar senha"
                    aria-label="Mostrar ou ocultar senha"
                    aria-pressed="false"
                >
                    <span class="fas fa-eye"></span>
                </button>
                @if ($errors->has('password'))
                    <div class="invalid-feedback d-block">
                        <strong>{{ $errors->first('password') }}</strong>
                    </div>
                @endif
            </div>
        </div>

        <div class="ciop-auth-meta">
            <div class="icheck-primary">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember">{{ __('adminlte::adminlte.remember_me') }}</label>
            </div>
        </div>

        <button type="submit" class="btn {{ config('adminlte.classes_auth_btn', 'ciop-btn-login') }}">
            <span class="fas fa-sign-in-alt" aria-hidden="true"></span>
            {{ __('adminlte::adminlte.sign_in') }}
        </button>
    </form>
@stop

@section('auth_footer')
    <ul class="ciop-auth-links">
        @if ($password_reset_url)
            <li>
                <a href="{{ $password_reset_url }}">
                    {{ __('adminlte::adminlte.i_forgot_my_password') }}
                </a>
            </li>
        @endif
        <li>
            <a href="{{ url('/') }}">Voltar ao site</a>
        </li>
    </ul>
@stop

@section('js')
    <script>
        (function () {
            var btn = document.getElementById('toggle-password');
            var input = document.getElementById('password');
            if (!btn || !input) return;
            btn.addEventListener('click', function () {
                var show = input.getAttribute('type') === 'password';
                input.setAttribute('type', show ? 'text' : 'password');
                btn.setAttribute('aria-pressed', show ? 'true' : 'false');
                var icon = btn.querySelector('span');
                if (!icon) return;
                icon.classList.toggle('fa-eye', !show);
                icon.classList.toggle('fa-eye-slash', show);
            });
        })();
    </script>
@stop
