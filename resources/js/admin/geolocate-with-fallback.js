// Wrapper em torno de navigator.geolocation.getCurrentPosition com um fallback de
// timeout proprio. Alguns navegadores/SOs (ex.: Windows com Location Services desligado)
// nunca chamam nem o success nem o error callback do getCurrentPosition, ignorando a
// opcao "timeout" nativa da API — sem isso, a UI que espera a localizacao trava pra
// sempre. Extraido do form.blade.php de ocorrencias para ser testavel fora do browser.
function geolocateWithFallback(geolocation, callbacks, geoOptions) {
    var settled = false;
    var timeoutMs = (geoOptions && geoOptions.timeoutMs) || 11000;

    var fallback = setTimeout(function() {
        if (settled) return;
        settled = true;
        callbacks.onUnavailable();
    }, timeoutMs);

    geolocation.getCurrentPosition(
        function(position) {
            if (settled) return;
            settled = true;
            clearTimeout(fallback);
            callbacks.onSuccess(position);
        },
        function(error) {
            if (settled) return;
            settled = true;
            clearTimeout(fallback);
            callbacks.onUnavailable(error);
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
    );
}

// Não dá pra distinguir "rodando no Node" checando `typeof module` — o bundle do
// webpack também envolve este arquivo num wrapper CommonJS com module.exports (mesmo
// no browser), então essa checagem sozinha nunca chegaria a definir window.*. `window`
// só existe de verdade no browser, então ele decide qual branch importa.
if (typeof window !== 'undefined') {
    window.geolocateWithFallback = geolocateWithFallback;
}
if (typeof module !== 'undefined' && module.exports) {
    module.exports = geolocateWithFallback;
}
