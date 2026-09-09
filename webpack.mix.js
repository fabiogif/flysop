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
    .js('resources/js/admin/leaflet.js', 'public/js');
