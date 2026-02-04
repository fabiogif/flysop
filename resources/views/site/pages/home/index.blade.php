@extends('site.layouts.app')

@section('title', 'SOP | Sistema de Ocorrências Públicas')

@section('content')
    <section id="intro" class="w3l-about bg-light section-py" aria-labelledby="intro-title">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h2 id="intro-title" class="h3 mb-3">Comece a usar o SOP</h2>
                    <p class="text-muted mb-4">Cadastre sua organização e comece a registrar e acompanhar ocorrências com geolocalização e status em tempo real.</p>
                    <div class="sop-hero-cta justify-content-center">
                        <a href="{{ route('login') }}" class="btn btn-primary btn-style">Acessar o sistema</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-outline-primary">Cadastrar</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
