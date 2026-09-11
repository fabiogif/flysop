const test = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
const path = require('node:path');
const geolocateWithFallback = require('../../resources/js/admin/geolocate-with-fallback.js');

// Regressao: o webpack embrulha o arquivo num wrapper CommonJS mesmo no bundle do
// browser (module.exports sempre existe e e "truthy"), entao checar so `typeof module`
// pra decidir entre window.* e module.exports nunca chega a definir window.* de verdade.
// Simula esse ambiente (window existe + module.exports existe, como no bundle real).
test('bundle do browser: define window.geolocateWithFallback mesmo com module.exports presente (como no wrapper do webpack)', function() {
    var src = fs.readFileSync(
        path.join(__dirname, '../../resources/js/admin/geolocate-with-fallback.js'),
        'utf8'
    );
    var sandbox = { window: {}, module: { exports: {} } };
    vm.createContext(sandbox);
    vm.runInContext(src, sandbox);
    assert.equal(typeof sandbox.window.geolocateWithFallback, 'function');
});

test('sucesso: chama onSuccess e nao dispara o fallback', function(t, done) {
    var geolocation = {
        getCurrentPosition: function(onSuccess) {
            onSuccess({ coords: { latitude: -12.9, longitude: -38.4 } });
        }
    };
    geolocateWithFallback(geolocation, {
        onSuccess: function(position) {
            assert.equal(position.coords.latitude, -12.9);
            done();
        },
        onUnavailable: function() {
            assert.fail('onUnavailable nao deveria ser chamado quando ha sucesso');
        }
    }, { timeoutMs: 20 });
});

test('erro: chama onUnavailable imediatamente (permissao negada, etc.)', function(t, done) {
    var geolocation = {
        getCurrentPosition: function(onSuccess, onError) {
            onError({ code: 1, message: 'User denied Geolocation' });
        }
    };
    geolocateWithFallback(geolocation, {
        onSuccess: function() {
            assert.fail('onSuccess nao deveria ser chamado quando ha erro');
        },
        onUnavailable: function(error) {
            assert.equal(error.code, 1);
            done();
        }
    }, { timeoutMs: 20 });
});

test('nunca responde: fallback proprio chama onUnavailable apos o timeout (caso do bug relatado)', function(t, done) {
    var geolocation = {
        // Simula o navegador/SO que nunca invoca nenhum dos dois callbacks.
        getCurrentPosition: function() {}
    };
    geolocateWithFallback(geolocation, {
        onSuccess: function() {
            assert.fail('onSuccess nao deveria ser chamado');
        },
        onUnavailable: function(error) {
            assert.equal(error, undefined);
            done();
        }
    }, { timeoutMs: 20 });
});

test('sucesso tardio apos o fallback ja ter disparado nao chama onSuccess de novo', function(t, done) {
    var lateSuccess;
    var geolocation = {
        getCurrentPosition: function(onSuccess) {
            lateSuccess = onSuccess;
        }
    };
    geolocateWithFallback(geolocation, {
        onSuccess: function() {
            assert.fail('onSuccess nao deveria ser chamado apos o fallback ja ter resolvido');
        },
        onUnavailable: function() {
            lateSuccess({ coords: { latitude: 1, longitude: 1 } });
            setTimeout(done, 10);
        }
    }, { timeoutMs: 20 });
});
