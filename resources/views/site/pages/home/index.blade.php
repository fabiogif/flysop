@extends('site.layouts.app')

@section('title', 'CIOP — Central Inteligente de Ocorrências Públicas')

@section('content')
    {{-- Hero: uma composição, brand em primeiro plano --}}
    <section id="home" class="ciop-hero" aria-labelledby="hero-title">
        <div class="ciop-hero-media" aria-hidden="true">
            <img src="{{ asset('images/landing/geo-1.jpg') }}" alt="Celular com mapa de navegação urbana em tempo real" class="ciop-hero-img" width="1920" height="1080" fetchpriority="high">
            <div class="ciop-hero-veil"></div>
            <div class="ciop-hero-grid"></div>
        </div>
        <div class="container">
            <div class="ciop-hero-content">
            <p class="ciop-hero-brand" aria-hidden="true">CIOP</p>
            <h1 id="hero-title" class="ciop-hero-title">
                Operação pública com visão em tempo real
            </h1>
            <p class="ciop-hero-lead">
                Registre, despache e acompanhe ocorrências com mapa, protocolo e painel do agente em campo.
            </p>
            <div class="ciop-hero-cta">
                <a class="ciop-btn ciop-btn-solid" href="{{ route('login') }}">Acessar o sistema</a>
                <a class="ciop-btn ciop-btn-ghost" href="#recursos">Ver recursos</a>
            </div>
            </div>
        </div>
    </section>

    {{-- Paralaxe: obras / ocorrências públicas --}}
    <section id="obras" class="ciop-parallax ciop-parallax-obras ciop-parallax-light" data-parallax aria-labelledby="obras-title">
        <div class="ciop-parallax-media" aria-hidden="true">
            <div
                class="ciop-parallax-layer"
                data-parallax-layer
                data-speed="0.28"
                style="background-image: url('{{ asset('images/landing/obras-ilustracao.png') }}')"
            ></div>
            <div class="ciop-parallax-veil"></div>
        </div>
        <div class="container ciop-parallax-content reveal">
            <p class="ciop-parallax-kicker">Ocorrências urbanas</p>
            <h2 id="obras-title">Buracos, alagamentos e demandas da prefeitura</h2>
            <p>
                Registre e acompanhe ocorrências típicas de órgãos públicos: vias danificadas,
                alagamentos, reparos e outras solicitações da população.
            </p>
        </div>
    </section>

    {{-- Recursos da aplicação --}}
    <section id="recursos" class="ciop-section" aria-labelledby="recursos-title">
        <div class="container">
            <header class="ciop-section-head reveal">
                <h2 id="recursos-title">Recursos da aplicação</h2>
                <p>Tudo o que a central precisa para abrir, priorizar, despachar e concluir ocorrências.</p>
            </header>

            <div class="ciop-feature-grid">
                <article class="ciop-feature reveal">
                    <span class="ciop-feature-index" aria-hidden="true">01</span>
                    <h3>Protocolo e prioridades</h3>
                    <p>Cada ocorrência recebe protocolo, classificação e prioridade para triagem objetiva.</p>
                </article>
                <article class="ciop-feature reveal">
                    <span class="ciop-feature-index" aria-hidden="true">02</span>
                    <h3>Mapa e geolocalização</h3>
                    <p>Endereço e coordenadas no mapa operacional para orientar o deslocamento das equipes.</p>
                </article>
                <article class="ciop-feature reveal">
                    <span class="ciop-feature-index" aria-hidden="true">03</span>
                    <h3>Painel do agente</h3>
                    <p>Aceite, rejeição, atualização de status e envio de posição GPS em tempo real.</p>
                </article>
                <article class="ciop-feature reveal">
                    <span class="ciop-feature-index" aria-hidden="true">04</span>
                    <h3>Histórico de status</h3>
                    <p>Linha do tempo com mudanças de status, responsável e contexto de cada etapa.</p>
                </article>
                <article class="ciop-feature reveal">
                    <span class="ciop-feature-index" aria-hidden="true">05</span>
                    <h3>Equipes e departamentos</h3>
                    <p>Organize órgãos, departamentos e equipes para distribuir o atendimento com clareza.</p>
                </article>
                <article class="ciop-feature reveal">
                    <span class="ciop-feature-index" aria-hidden="true">06</span>
                    <h3>Notificações internas</h3>
                    <p>Alertas de atribuição, mudança de status e SLA para quem precisa agir agora.</p>
                </article>
                <article class="ciop-feature reveal">
                    <span class="ciop-feature-index" aria-hidden="true">07</span>
                    <h3>Perfis e permissões</h3>
                    <p>Controle fino de acesso: administrador, supervisor, atendente e agente de campo.</p>
                </article>
                <article class="ciop-feature reveal">
                    <span class="ciop-feature-index" aria-hidden="true">08</span>
                    <h3>Dashboard operacional</h3>
                    <p>Indicadores, ocorrências recentes e visão consolidada para decisões rápidas.</p>
                </article>
                <article class="ciop-feature reveal">
                    <span class="ciop-feature-index" aria-hidden="true">09</span>
                    <h3>Auditoria</h3>
                    <p>Registro de logins, convites, mudanças de cargo e ações administrativas sensíveis.</p>
                </article>
                <article class="ciop-feature reveal">
                    <span class="ciop-feature-index" aria-hidden="true">10</span>
                    <h3>Multi-organização</h3>
                    <p>Cada tenant gerencia usuários, branding e configurações da própria central.</p>
                </article>
                <article class="ciop-feature reveal">
                    <span class="ciop-feature-index" aria-hidden="true">11</span>
                    <h3>Convite de membros</h3>
                    <p>Inclua a equipe por e-mail, com cargo inicial e aceite seguro do convite.</p>
                </article>
                <article class="ciop-feature reveal">
                    <span class="ciop-feature-index" aria-hidden="true">12</span>
                    <h3>API e canais</h3>
                    <p>Integração por API para consulta e operação conectada a sistemas externos.</p>
                </article>
            </div>
        </div>
    </section>

    {{-- Paralaxe: geolocalização --}}
    <section id="mapa" class="ciop-parallax ciop-parallax-geo" data-parallax aria-labelledby="mapa-title">
        <div class="ciop-parallax-media" aria-hidden="true">
            <div
                class="ciop-parallax-layer"
                data-parallax-layer
                data-speed="0.32"
                style="background-image: url('{{ asset('images/landing/geo-1.jpg') }}')"
            ></div>
            <div
                class="ciop-parallax-layer ciop-parallax-layer-soft"
                data-parallax-layer
                data-speed="0.16"
                style="background-image: url('{{ asset('images/landing/geo-2.jpg') }}')"
            ></div>
            <div class="ciop-parallax-veil ciop-parallax-veil-geo"></div>
        </div>
        <div class="container ciop-parallax-content reveal">
            <p class="ciop-parallax-kicker">Geolocalização</p>
            <h2 id="mapa-title">Mapa no celular, bairros e cidades</h2>
            <p>
                Visualize a ocorrência no mapa do celular, na malha do bairro e na visão da cidade
                para orientar equipes com coordenadas precisas.
            </p>
        </div>
    </section>

    {{-- Galeria visual obras + geo --}}
    <section id="cenas" class="ciop-section ciop-scenes" aria-labelledby="cenas-title">
        <div class="container">
            <header class="ciop-section-head reveal">
                <h2 id="cenas-title">Ocorrências e mapas</h2>
                <p>Do buraco na via ao alagamento; do GPS no celular ao mapa do bairro.</p>
            </header>
            <div class="ciop-scene-grid">
                <figure class="ciop-scene reveal" data-parallax-scene data-speed="0.08">
                    <img src="{{ asset('images/landing/obras-1.jpg') }}" alt="Reparo de buraco em via pública com compactação de asfalto" width="960" height="640" loading="lazy">
                    <figcaption>Reparo de buraco em via pública</figcaption>
                </figure>
                <figure class="ciop-scene reveal" data-parallax-scene data-speed="0.12">
                    <img src="{{ asset('images/landing/geo-1.jpg') }}" alt="Celular com mapa de navegação GPS" width="960" height="640" loading="lazy">
                    <figcaption>Mapa de navegação no celular</figcaption>
                </figure>
                <figure class="ciop-scene reveal" data-parallax-scene data-speed="0.1">
                    <img src="{{ asset('images/landing/obras-2.jpg') }}" alt="Rua alagada com veículos e infraestrutura urbana" width="960" height="640" loading="lazy">
                    <figcaption>Alagamento em via urbana</figcaption>
                </figure>
                <figure class="ciop-scene reveal" data-parallax-scene data-speed="0.14">
                    <img src="{{ asset('images/landing/geo-2.jpg') }}" alt="Mapa rodoviário de bairros e cidades" width="960" height="640" loading="lazy">
                    <figcaption>Mapa de bairros e cidades</figcaption>
                </figure>
                <figure class="ciop-scene reveal" data-parallax-scene data-speed="0.09">
                    <img src="{{ asset('images/landing/obras-3.jpg') }}" alt="Asfalto rachado e remendos em via pública" width="960" height="640" loading="lazy">
                    <figcaption>Via danificada — ocorrência de pavimento</figcaption>
                </figure>
                <figure class="ciop-scene reveal" data-parallax-scene data-speed="0.13">
                    <img src="{{ asset('images/landing/geo-3.jpg') }}" alt="Smartphone no painel do carro com rota no mapa" width="960" height="640" loading="lazy">
                    <figcaption>GPS móvel em deslocamento</figcaption>
                </figure>
            </div>
        </div>
    </section>

    {{-- Fluxo --}}
    <section id="fluxo" class="ciop-section ciop-section-alt" aria-labelledby="fluxo-title">
        <div class="container">
            <header class="ciop-section-head reveal">
                <h2 id="fluxo-title">Como funciona</h2>
                <p>Do registro ao fechamento, em um fluxo contínuo.</p>
            </header>
            <ol class="ciop-flow reveal">
                <li>
                    <strong>Abertura</strong>
                    <span>Cadastro com endereço, tipo e prioridade</span>
                </li>
                <li>
                    <strong>Triagem</strong>
                    <span>Classificação e definição de urgência</span>
                </li>
                <li>
                    <strong>Despacho</strong>
                    <span>Atribuição a equipe ou agente</span>
                </li>
                <li>
                    <strong>Campo</strong>
                    <span>GPS, status e evidências no deslocamento</span>
                </li>
                <li>
                    <strong>Conclusão</strong>
                    <span>Histórico, auditoria e indicadores</span>
                </li>
            </ol>
        </div>
    </section>

    {{-- Para quem --}}
    <section id="para-quem" class="ciop-section" aria-labelledby="para-quem-title">
        <div class="container">
            <header class="ciop-section-head reveal">
                <h2 id="para-quem-title">Para quem é o CIOP</h2>
                <p>Perfis alinhados à operação de ocorrências públicas.</p>
            </header>
            <div class="ciop-roles">
                <div class="ciop-role reveal">
                    <h3>Gestão e administração</h3>
                    <p>Configura usuários, prioridades, órgãos e acompanha a operação no dashboard.</p>
                </div>
                <div class="ciop-role reveal">
                    <h3>Central / supervisão</h3>
                    <p>Triagem, despacho, SLA e visão do mapa com equipes em campo.</p>
                </div>
                <div class="ciop-role reveal">
                    <h3>Agente de campo</h3>
                    <p>Recebe ocorrências, atualiza status e compartilha posição durante o atendimento.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA final --}}
    <section class="ciop-cta-band" aria-labelledby="cta-title">
        <div class="container ciop-cta-inner reveal">
            <h2 id="cta-title">Pronto para operar com o CIOP?</h2>
            <p>Entre com sua conta e use o painel da central agora.</p>
            <a href="{{ route('login') }}" class="ciop-btn ciop-btn-solid">Acessar o sistema</a>
        </div>
    </section>
@endsection
