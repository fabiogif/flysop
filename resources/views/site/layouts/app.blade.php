<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="SOP – Sistema de Ocorrências Públicas. Gestão de ocorrências com geolocalização e acompanhamento em tempo real.">
    <title>@yield('title', 'SOP | Sistema de Ocorrências Públicas')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style-starter.css') }}">
    <link rel="stylesheet" href="{{ asset('css/site-theme.css') }}">
    @stack('styles')
</head>
<body>
    <a href="#main-content" class="skip-link">Ir para o conteúdo</a>

    <header id="site-header" class="fixed-top" role="banner">
        <nav class="navbar navbar-expand-lg navbar-dark" aria-label="Navegação principal">
            <a class="navbar-brand" href="{{ url('/') }}">
                SOP <span class="fa fa-shield" aria-hidden="true"></span>
            </a>
            <button class="navbar-toggler bg-gradient" type="button" data-toggle="collapse" data-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Abrir menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav m-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/') }}">Início</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/') }}#services">Serviços</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/') }}#about">Sobre</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a href="{{ route('login') }}" class="btn btn-primary btn-style">Acessar</a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <section id="home" class="banner" aria-labelledby="hero-title">
        <div id="banner-bg-effect" class="banner-effect" aria-hidden="true"></div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7 col-md-12 col-sm-12 order-lg-first mt-lg-0 mt-4">
                    <h1 id="hero-title" class="mb-4 title sop-hero-title">
                        <strong>SOP</strong> – Sistema de Ocorrências Públicas
                    </h1>
                    <p class="mb-0">Gestão de ocorrências com geolocalização e acompanhamento em tempo real.</p>
                    <div class="sop-hero-cta">
                        <a class="btn btn-outline btn-outline-style" href="{{ route('login') }}">Acessar o sistema</a>
                        @if (Route::has('register'))
                            <a class="btn btn-primary btn-style" href="{{ route('register') }}">Cadastrar</a>
                        @endif
                    </div>
                </div>
                <div class="col-lg-5 col-md-12 col-sm-12 order-first text-lg-left text-center">
                    <img src="{{ asset('images/geo.jpg') }}" alt="" class="rounded-circle img-fluid" width="300" height="300" loading="lazy">
                </div>
            </div>
        </div>
    </section>

    <main id="main-content" class="site-main" role="main" tabindex="-1">
        @hasSection('content')
            @yield('content')
        @endif

        <section id="about" class="w3l-about section-py" aria-labelledby="about-title">
            <div class="container">
                <div class="row about-content align-items-center">
                    <div class="col-lg-6 info mb-lg-0 mb-4">
                        <h2 id="about-title" class="title">Sobre o sistema</h2>
                        <p class="mt-3 mb-0">O SOP centraliza o cadastro e o acompanhamento de ocorrências públicas, com suporte a geolocalização e status em tempo real, facilitando a gestão e a tomada de decisão.</p>
                    </div>
                    <div class="col-lg-6">
                        <img src="{{ asset('images/about.png') }}" class="img-fluid img-shadow" alt="Ilustração sobre o sistema SOP" width="500" height="350" loading="lazy">
                    </div>
                </div>
            </div>
        </section>

        <section id="services" class="bg-light section-py" aria-labelledby="services-title">
            <div class="container">
                <div class="row align-items-center mb-4">
                    <div class="col-lg-8 offset-lg-2 col-md-12 text-center">
                        <h2 id="services-title" class="section-title">O que o SOP oferece</h2>
                        <p class="text-muted mb-0">Ferramentas para registrar, acompanhar e resolver ocorrências de forma organizada e transparente.</p>
                    </div>
                </div>
                <div class="row mt-lg-4">
                    <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                        <div class="sop-service-card">
                            <div class="icon mb-2"><span class="fa fa-map-marker" aria-hidden="true"></span></div>
                            <h3>Geolocalização</h3>
                            <p>Registro da localização exata de cada ocorrência para deslocamento e acompanhamento no mapa.</p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                        <div class="sop-service-card">
                            <div class="icon mb-2"><span class="fa fa-list-alt" aria-hidden="true"></span></div>
                            <h3>Status em tempo real</h3>
                            <p>Acompanhe o andamento: aberta, em atendimento ou finalizada, com atualizações visíveis no dashboard.</p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                        <div class="sop-service-card">
                            <div class="icon mb-2"><span class="fa fa-shield" aria-hidden="true"></span></div>
                            <h3>Gestão integrada</h3>
                            <p>Cadastro de órgãos, tipos de ocorrência e usuários em um único sistema, com controle de acesso.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="stats" class="stats section-py" aria-labelledby="stats-title">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-5 mb-4 mb-lg-0">
                        <h2 id="stats-title" class="left-title">Monitoramento em tempo real</h2>
                        <p class="white mb-0">Acompanhe ocorrências, status e indicadores diretamente no painel, para decisões mais rápidas e transparentes.</p>
                    </div>
                    <div class="col-lg-7">
                        <div class="d-sm-flex justify-content-lg-around justify-content-between counter-main" role="list">
                            <div class="counter" role="listitem">
                                <div class="icon"><span class="fa fa-folder-open-o" aria-hidden="true"></span></div>
                                <p class="value">Ocorrências</p>
                                <p class="title white">Cadastro centralizado</p>
                            </div>
                            <div class="counter" role="listitem">
                                <div class="icon"><span class="fa fa-map-marker" aria-hidden="true"></span></div>
                                <p class="value">Mapa</p>
                                <p class="title white">Geolocalização</p>
                            </div>
                            <div class="counter" role="listitem">
                                <div class="icon"><span class="fa fa-check-circle" aria-hidden="true"></span></div>
                                <p class="value">Status</p>
                                <p class="title white">Acompanhamento</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer id="site-footer" role="contentinfo">
        <div class="top-footer">
            <div class="container my-md-5 my-4">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="footer-logo mb-3">
                            <a href="{{ url('/') }}"><span class="fa fa-shield" aria-hidden="true"></span> SOP</a>
                        </div>
                        <p class="mb-0">Sistema de Ocorrências Públicas – gestão de ocorrências com geolocalização e monitoramento em tempo real.</p>
                    </div>
                    <div class="col-lg-6 mt-4 mt-lg-0 text-lg-right">
                        <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm">Acessar o sistema</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="bottom-footer">
            <div class="container">
                <div class="row">
                    <div class="col-12 text-center py-3">
                        <p class="copyright mb-0">© {{ date('Y') }} SOP – Sistema de Ocorrências Públicas</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <button type="button" id="movetop" class="bg-primary" aria-label="Voltar ao topo" title="Voltar ao topo" style="display: none;">
        <span class="fa fa-angle-up" aria-hidden="true"></span>
    </button>

    @vite(['resources/js/site.js'])
    @stack('scripts')
</body>
</html>
