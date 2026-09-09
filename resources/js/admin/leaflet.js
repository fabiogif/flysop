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
