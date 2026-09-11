const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .vue()
    .sass('resources/sass/app.scss', 'public/css')
    // Leaflet isolado (sem o resto do bundle "app.js", que reatribui window.$/jQuery e
    // quebraria os plugins jQuery já carregados nas páginas com mapa — ver
    // resources/js/admin/leaflet.js) para as páginas admin com mapa (ocorrência,
    // despacho, dashboard) que hoje não carregam nenhum bundle JS compilado.
    .js('resources/js/admin/leaflet.js', 'public/js')
    // Wrapper de geolocalizacao com fallback de timeout (ver comentario no proprio
    // arquivo) — isolado do resto pra poder ser testado com `npm run test:js`
    // (tests/js/geolocate-with-fallback.test.js) sem depender do browser.
    .js('resources/js/admin/geolocate-with-fallback.js', 'public/js')
    // Imagens do ícone padrão do marcador (Leaflet resolve a URL delas relativa à página
    // atual quando bundlado via require() em vez do <link>/script oficial do pacote —
    // sem isso, 404 em /admin/{qualquer-rota}/marker-icon.png). URLs fixas em
    // resources/js/admin/leaflet.js apontam pra cá.
    .copyDirectory('node_modules/leaflet/dist/images', 'public/images/leaflet')
    // O CSS oficial do Leaflet (.leaflet-pane, .leaflet-tile-pane etc. com
    // position:absolute) nunca era carregado nas paginas admin com mapa — elas
    // estendem adminlte::page, que nao inclui app.css (onde o leaflet.css entra via
    // @import no app.scss). Sem ele, os tiles nao ficam posicionados e o mapa fica em
    // branco mesmo com o JS funcionando. Copiado isolado, como as imagens acima.
    .copy('node_modules/leaflet/dist/leaflet.css', 'public/css/leaflet.css')
    // Sem isso, arquivos compilados (js/css) mantêm sempre o mesmo nome de URL — com o
    // nginx cacheando estático por 7 dias (deploy-fly/nginx.conf), quem já tinha visitado
    // o admin fica preso numa versão antiga do JS até o cache expirar sozinho. version()
    // adiciona "?id=hash" na URL (via mix(), não asset(), nas views) e muda a cada build.
    .version();
