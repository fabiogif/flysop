window._ = require('lodash');

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

try {
    window.Popper = require('popper.js').default;
    window.$ = window.jQuery = require('jquery');

    require('bootstrap');
} catch (e) {}

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Leaflet + plugins (cluster/heat) — substitui o Google Maps JS API (exigia billing
 * habilitado no Google Cloud, sem alternativa gratuita real). Exposto em window.L para
 * os scripts inline de cada página (padrão já usado no projeto: JS de mapa por página,
 * sem módulo compartilhado — ver resources/views/admin/pages/home/index.blade.php etc.).
 * OpenStreetMap como provedor de tiles (sem chave); Nominatim para geocoding — ambos
 * gratuitos e sem cartão de crédito, ver docs/specs/modules.md para limites de uso.
 */
window.L = require('leaflet');
require('leaflet.markercluster');
require('leaflet.heat');

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo';

window.Pusher = require('pusher-js');

// Suporta Pusher SaaS (MIX_PUSHER_WS_HOST vazio -> host oficial via cluster + TLS)
// ou um servidor compatível self-hosted como Soketi (MIX_PUSHER_WS_HOST/WS_PORT, sem TLS).
const pusherForceTLS = process.env.MIX_PUSHER_FORCE_TLS !== 'false';

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    wsHost: process.env.MIX_PUSHER_WS_HOST || undefined,
    wsPort: process.env.MIX_PUSHER_WS_PORT || undefined,
    wssPort: process.env.MIX_PUSHER_WS_PORT || undefined,
    forceTLS: pusherForceTLS,
    enabledTransports: pusherForceTLS ? ['ws', 'wss'] : ['ws'],
    disableStats: true,
});
