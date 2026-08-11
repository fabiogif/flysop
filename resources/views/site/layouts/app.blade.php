<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="CIOP — Central Inteligente de Ocorrências Públicas. Protocolo, mapa, despacho, GPS do agente e acompanhamento operacional em tempo real.">
    <title>@yield('title', 'CIOP — Central Inteligente de Ocorrências Públicas')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style-starter.css') }}">
    <link rel="stylesheet" href="{{ asset('css/site-theme.css') }}">
    @stack('styles')
</head>
<body class="ciop-body">
    <a href="#main-content" class="skip-link">Ir para o conteúdo</a>

    <header id="site-header" class="ciop-header" role="banner">
        <nav class="navbar navbar-expand-lg ciop-nav" aria-label="Navegação principal">
            <div class="container ciop-nav-inner">
                <a class="ciop-brand" href="{{ url('/') }}">
                    <span class="ciop-brand-mark">CIOP</span>
                </a>
                <button class="navbar-toggler ciop-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Abrir menu">
                    <span class="ciop-toggler-bar"></span>
                    <span class="ciop-toggler-bar"></span>
                    <span class="ciop-toggler-bar"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ciop-nav-links ml-lg-auto">
                        <li class="nav-item"><a class="nav-link" href="{{ url('/') }}#recursos">Recursos</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('/') }}#fluxo">Como funciona</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('/') }}#para-quem">Para quem</a></li>
                        <li class="nav-item d-lg-none"><a class="nav-link" href="{{ route('login') }}">Acessar</a></li>
                    </ul>
                    <div class="ciop-nav-cta d-none d-lg-flex">
                        <a href="{{ route('login') }}" class="ciop-btn ciop-btn-solid">Acessar</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main id="main-content" class="site-main" role="main" tabindex="-1">
        @yield('content')
    </main>

    <footer id="site-footer" class="ciop-footer" role="contentinfo">
        <div class="container">
            <div class="ciop-footer-row">
                <div>
                    <p class="ciop-footer-brand">CIOP</p>
                    <p class="ciop-footer-text">Central Inteligente de Ocorrências Públicas</p>
                </div>
                <a href="{{ route('login') }}" class="ciop-btn ciop-btn-ghost">Entrar no sistema</a>
            </div>
            <p class="ciop-footer-copy">© {{ date('Y') }} CIOP</p>
        </div>
    </footer>

    <button type="button" id="movetop" class="ciop-movetop" aria-label="Voltar ao topo" title="Voltar ao topo" hidden>
        <span aria-hidden="true">↑</span>
    </button>

    <script src="{{ asset('js/site.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
