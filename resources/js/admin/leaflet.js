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

// Icon.Default._getIconUrl SEMPRE concatena IconDefault.imagePath na frente da url,
// mesmo quando ela já é absoluta (ver leaflet-src.js) — com leaflet.css carregado
// (necessário pro .leaflet-pane{position:absolute} funcionar), o auto-detect de
// imagePath acha a regra ".leaflet-default-icon-path" do CSS e retorna "/css/images/",
// gerando "/css/images//images/leaflet/marker-icon.png" (404). imagePath = '' (string,
// não undefined) pula esse auto-detect e deixa nossas urls absolutas intactas.
L.Icon.Default.imagePath = '';
// Sem isso, o ícone padrão do marcador tenta carregar de uma URL relativa à página atual
// (ex.: /admin/occurrences/marker-icon.png) em vez do caminho real — 404 silencioso,
// marcador invisível. Imagens copiadas de node_modules/leaflet/dist/images via
// webpack.mix.js (copyDirectory), path absoluto pra funcionar em qualquer rota admin.
L.Icon.Default.mergeOptions({
    iconRetinaUrl: '/images/leaflet/marker-icon-2x.png',
    iconUrl: '/images/leaflet/marker-icon.png',
    shadowUrl: '/images/leaflet/marker-shadow.png',
});
