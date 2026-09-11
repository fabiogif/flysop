/**
 * Entrada Vite dedicada só ao Leaflet (window.L), para as páginas admin que têm mapa
 * (formulário de ocorrência, central de despacho, dashboard) sem precisar carregar o
 * bundle legado inteiro (resources/js/app.js) — esse reatribui window.$/jQuery a uma
 * nova instância, o que quebra os plugins jQuery (Select2/DataTables/SweetAlert2/
 * Inputmask) já carregados via <script> do AdminLTE nessas mesmas páginas.
 */
window.L = require('leaflet');
require('leaflet.markercluster');
require('leaflet.heat');

// Sem isso, o ícone padrão do marcador tenta carregar de uma URL relativa à página atual
// (ex.: /admin/occurrences/marker-icon.png) em vez do caminho real — 404 silencioso,
// marcador invisível. Imagens copiadas de node_modules/leaflet/dist/images via
// webpack.mix.js (copyDirectory), path absoluto pra funcionar em qualquer rota admin.
L.Icon.Default.mergeOptions({
    iconRetinaUrl: '/images/leaflet/marker-icon-2x.png',
    iconUrl: '/images/leaflet/marker-icon.png',
    shadowUrl: '/images/leaflet/marker-shadow.png',
});
