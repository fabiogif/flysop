@extends('adminlte::master')

@php( $dashboard_url = View::getSection('dashboard_url') ?? config('adminlte.dashboard_url', 'home') )

@if (config('adminlte.use_route_url', false))
    @php( $dashboard_url = $dashboard_url ? route($dashboard_url) : '' )
@else
    @php( $dashboard_url = $dashboard_url ? url($dashboard_url) : '' )
@endif

@section('adminlte_css')
    @stack('css')
    @yield('css')
@stop

@section('classes_body'){{ ($auth_type ?? 'login') . '-page ciop-auth-page' }}@stop

@section('body')
    <div class="ciop-auth">
        <aside
            class="ciop-auth-visual"
            style="--ciop-auth-bg: url('{{ asset('images/landing/geo-1.jpg') }}')"
            aria-hidden="false"
        >
            <div class="ciop-auth-visual-inner">
                <p class="ciop-auth-kicker">Central operacional</p>
                <h1>CIOP</h1>
                <p>
                    Central Inteligente de Ocorrências Públicas — registro, mapa e equipes
                    em um só painel.
                </p>
            </div>
        </aside>

        <main class="ciop-auth-panel">
            <div class="ciop-auth-shell">
                <a class="ciop-auth-brand" href="{{ url('/') }}">
                    <img src="{{ asset(config('adminlte.logo_img', 'images/ciop-mark.svg')) }}" alt="CIOP">
                    <span class="ciop-auth-brand-text">
                        <strong>CIOP</strong>
                        <span>Acesso ao sistema</span>
                    </span>
                </a>

                <div class="card {{ config('adminlte.classes_auth_card', 'ciop-auth-card-inner') }}">
                    @hasSection('auth_header')
                        <div class="card-header {{ config('adminlte.classes_auth_header', '') }}">
                            <h2 class="card-title float-none text-center">
                                @yield('auth_header')
                            </h2>
                        </div>
                    @endif

                    <div class="card-body {{ $auth_type ?? 'login' }}-card-body {{ config('adminlte.classes_auth_body', '') }}">
                        @yield('auth_body')
                    </div>

                    @hasSection('auth_footer')
                        <div class="card-footer {{ config('adminlte.classes_auth_footer', '') }}">
                            @yield('auth_footer')
                        </div>
                    @endif
                </div>
            </div>
        </main>
    </div>
@stop

@section('adminlte_js')
    @stack('js')
    @yield('js')
@stop
