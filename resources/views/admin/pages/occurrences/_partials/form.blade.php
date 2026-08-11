@include('admin.includes.alerts')
<?php
if (!isset($occurrences)) {
    $typeoccurrences_id = '';
    $status_occurrences_id = '';
    $issuingsId = '';
    $driverId = '';
} else {
    $typeoccurrences_id = $occurrences->type_occurrences_id;
    $status_occurrences_id = $occurrences->status_occurrences_id;
    $issuingsId = $occurrences->issuings_id;
    $driverId = $occurrences->driver_id ?? '';
}
$drivers = $drivers ?? collect();
$priorities = $priorities ?? collect();
$priorityId = $occurrences->priority_id ?? '';
?>
<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
        <div class="row">
            <div class="col-lg-8 col-md-8 col-sm-6 col-xs-12">
                <div class="form-group">
                    <label for="name">Nome Completo:</label>
                    <input type="text" name="name" id="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                        placeholder="Informe o Nome Completo" value="{{ $occurrences->name ?? old('name') }}">
                    @error('name') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8 col-md-8 col-sm-6 col-xs-12">
                <div class="form-group">
                    <label for="title">Título:</label>
                    <input type="text" name="title" id="title" class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}" placeholder="Informe o título"
                        value="{{ $occurrences->title ?? old('title') }}">
                    @error('title') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8 col-md-8 col-sm-6 col-xs-12">
                <div class="form-group">
                    <label for="location-input">Endereço:</label>
                    <input type="text" name="address" id="location-input" class="form-control {{ $errors->has('address') ? 'is-invalid' : '' }}"
                        placeholder="Digite o endereço ou clique no mapa para marcar" autocomplete="off" required
                        value="{{ $occurrences->address ?? old('address') }}">
                    @error('address') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                <div class="form-group">
                    <label for="sublocality-input">Bairro:</label>
                    <input type="text" name="neighborhood" id="sublocality-input" readonly class="form-control"
                        placeholder="Preenchido pelo mapa" value="{{ $occurrences->neighborhood ?? old('neighborhood') }}">
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                <div class="form-group">
                    <label for="locality-input">Cidade:</label>
                    <input type="text" name="city" id="locality-input" readonly class="form-control"
                        value="{{ $occurrences->city ?? old('city') }}">
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                <div class="form-group">
                    <label for="administrative_area_level_1-input">Estado:</label>
                    <input type="text" name="state" class="form-control" readonly
                        id="administrative_area_level_1-input"
                        value="{{ $occurrences->state ?? old('state') }}">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8 col-md-8 col-sm-6 col-xs-12">
                <div class="form-group">
                    <label for="postal_code-input">CEP:</label>
                    <input type="text" name="zip" id="postal_code-input" readonly class="form-control"
                        placeholder="Preenchido pelo mapa" value="{{ $occurrences->zip ?? old('zip') }}">
                </div>
            </div>
        </div>
        <input type="hidden" value="Brasil" id="country-input" name="country">

        <div class="row">
            <div class="col-lg-8 col-md-8 col-sm-6 col-xs-12">
                <div class="form-group">
                    <label for="email">E-mail:</label>
                    <input type="email" name="email" id="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" placeholder="Informe o e-mail"
                        value="{{ $occurrences->email ?? old('email') }}">
                    @error('email') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8 col-md-8 col-sm-6 col-xs-12">
                <div class="form-group">
                    <label for="phone">Telefone:</label>
                    <input type="tel" name="phone" class="form-control phone" placeholder="Informe o Telefone"
                        required value="{{ $occurrences->phone ?? old('phone') }}">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                <div class="form-group">
                    <label for="latitude">Latitude:</label>
                    <input type="text" name="latitude" class="form-control" id="latitude" readonly
                        placeholder="Clique no mapa" value="{{ $occurrences->latitude ?? old('latitude') }}">
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                <div class="form-group">
                    <label for="longitude">Longitude:</label>
                    <input type="text" name="longitude" class="form-control" id="longitude" readonly
                        placeholder="Clique no mapa" value="{{ $occurrences->longitude ?? old('longitude') }}">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                <div class="form-group">
                    <label for="status_occurrences_id">Status Ocorrência:</label>
                    <select name="status_occurrences_id" id="status_occurrences_id" class="form-control {{ $errors->has('status_occurrences_id') ? 'is-invalid' : '' }}">
                        <option value="">Selecione o status...</option>
                        @foreach ($statusOccurrences as $key => $statusOccurrence)
                            <option value="{{ $statusOccurrence->id }}"
                                {{ (string)$statusOccurrence->id === (string)($status_occurrences_id ?? old('status_occurrences_id')) ? 'selected' : '' }}>
                                {{ $statusOccurrence->name }}</option>
                        @endforeach
                    </select>
                    @error('status_occurrences_id') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                <div class="form-group">
                    <label for="type_occurrences_id">Tipo Ocorrência:</label>
                    <select name="type_occurrences_id" class="form-control" required>
                        <option value="">Selecione...</option>
                        @foreach ($typeOccurrences as $key => $typeOccurrence)
                            <option value="{{ $typeOccurrence->id }}"
                                {{ $typeOccurrence->id == $typeoccurrences_id ? 'selected' : '' }}>
                                {{ $typeOccurrence->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                <div class="form-group">
                    <label for="issuings_id">Órgão:</label>
                    <select name="issuings_id" id="issuings_id" class="form-control" required>
                        <option value="">Selecione...</option>
                        @foreach ($issuings as $key => $issuing)
                            <option value="{{ $issuing->id }}" {{ $issuing->id == $issuingsId ? 'selected' : '' }}>
                                {{ $issuing->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                <div class="form-group">
                    <label for="driver_id">Motorista:</label>
                    <select name="driver_id" id="driver_id" class="form-control">
                        <option value="">Nenhum</option>
                        @foreach ($drivers as $driver)
                            <option value="{{ $driver->id }}" {{ (string)($driverId ?? old('driver_id')) === (string)$driver->id ? 'selected' : '' }}>
                                {{ $driver->name }}</option>
                        @endforeach
                    </select>
                    @if (isset($occurrences))
                        <button type="button" id="suggest-drivers-btn" class="btn btn-outline-secondary btn-sm mt-1">
                            <i class="fas fa-route"></i> Sugerir mais próximo
                        </button>
                        <div id="suggest-drivers-results" class="mt-1 small"></div>
                    @endif
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                <div class="form-group">
                    <label for="priority_id">Prioridade:</label>
                    <select name="priority_id" id="priority_id" class="form-control">
                        <option value="">Não definida</option>
                        @foreach ($priorities as $priority)
                            <option value="{{ $priority->id }}" {{ (string)($priorityId ?? old('priority_id')) === (string)$priority->id ? 'selected' : '' }}>
                                {{ $priority->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                <div class="form-group">
                    <label>Anexo:</label>
                    <input type="file" name="anexo[]" multiple class="form-control">
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                <div class="form-group">
                    <label for="anexo_phase">Fase da evidência:</label>
                    <select name="anexo_phase" id="anexo_phase" class="form-control">
                        <option value="">Não classificada</option>
                        <option value="antes" {{ old('anexo_phase') === 'antes' ? 'selected' : '' }}>Antes</option>
                        <option value="depois" {{ old('anexo_phase') === 'depois' ? 'selected' : '' }}>Depois</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8 col-md-8 col-sm-6 col-xs-12">
                <div class="form-group">
                    <label for="description">Descrição:</label>
                    <textarea cols="40" rows="5" name="description" id="description" required class="form-control">{{ $occurrences->description ?? old('description') }}</textarea>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">
            <div class="form-group">
                <button type="submit" class="btn btn-block btn-success">Salvar</button>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
        <div class="card">
            <div class="card-body">
                <p class="text-muted small mb-2">
                    <strong>Localização:</strong> Clique no mapa para marcar o ponto ou digite o endereço no campo "Endereço". O endereço será preenchido automaticamente ao marcar no mapa. Você também pode arrastar o marcador para ajustar.
                </p>
                <div id="occurrence-map-container" class="position-relative" style="min-height: 450px;">
                    <div id="occurrence-map-loading" class="position-absolute top-0 left-0 right-0 bottom-0 d-flex align-items-center justify-content-center bg-light border rounded">
                        <div class="text-center">
                            <div class="spinner-border text-primary mb-2" role="status" aria-hidden="true"></div>
                            <p class="mb-0 text-muted">Carregando mapa…</p>
                        </div>
                    </div>
                    <div id="occurrence-map" style="height: 450px; display: none;"></div>
                    <p id="occurrence-map-geocode-status" class="small text-muted mt-2 mb-0" style="display: none;" role="status" aria-live="polite">Buscando endereço…</p>
                </div>
            </div>
        </div>
    </div>
</div>

@php
    $initialLat = isset($occurrences) && $occurrences->latitude ? (float) $occurrences->latitude : -12.95307;
    $initialLng = isset($occurrences) && $occurrences->longitude ? (float) $occurrences->longitude : -38.49706;
    $mapsKey = config('services.google.maps_key') ?: env('GOOGLE_MAPS_API_KEY') ?: 'AIzaSyAKZUfI6dgn5kzjDSu9MKT84yhMW5UR5M0';
@endphp
<script>
(function() {
    "use strict";
    var MAP_CONTAINER_ID = 'occurrence-map';
    var LOADING_ID = 'occurrence-map-loading';
    var GEOCODE_STATUS_ID = 'occurrence-map-geocode-status';
    var CENTER = { lat: {{ $initialLat }}, lng: {{ $initialLng }} };
    var API_KEY = @json($mapsKey);
    var ZOOM = 17;

    function getEl(id) { return document.getElementById(id); }
    function getInput(name) {
        var el = getEl(name + '-input');
        return el || getEl(name);
    }

    function fillFormFromPlace(place) {
        var locInput = getInput('location');
        if (!locInput) return;
        if (place.formatted_address) locInput.value = place.formatted_address;
        if (!place.address_components) return;
        var fmt = { street_number: 'short_name', route: 'long_name', locality: 'long_name',
            sublocality_level_1: 'long_name',
            administrative_area_level_1: 'short_name', country: 'long_name', postal_code: 'short_name' };
        function comp(type) {
            for (var i = 0; i < place.address_components.length; i++) {
                var c = place.address_components[i];
                if (c.types.indexOf(type) !== -1) return c[fmt[type]] || '';
            }
            return '';
        }
        if (!place.formatted_address) {
            var street = (comp('street_number') + ' ' + comp('route')).trim();
            locInput.value = street || '';
        }
        var localityEl = getInput('locality');
        var stateEl = getInput('administrative_area_level_1');
        var zipEl = getInput('postal_code');
        var sublocalityEl = getInput('sublocality');
        if (localityEl) localityEl.value = comp('locality');
        if (stateEl) stateEl.value = comp('administrative_area_level_1');
        if (zipEl) zipEl.value = comp('postal_code');
        if (sublocalityEl) sublocalityEl.value = comp('sublocality_level_1');
        if (getEl('country-input')) getEl('country-input').value = comp('country') || 'Brasil';
    }

    function setLatLng(lat, lng) {
        var latEl = getEl('latitude');
        var lngEl = getEl('longitude');
        if (latEl) latEl.value = lat;
        if (lngEl) lngEl.value = lng;
    }

    function showMapError(msg) {
        var loadingEl = getEl(LOADING_ID);
        if (loadingEl) {
            loadingEl.style.display = 'block';
            loadingEl.innerHTML = '<div class="p-3 text-center text-danger"><p class="mb-0">' + (msg || 'Não foi possível carregar o mapa.') + '</p><p class="small mt-2">Verifique a chave da API (GOOGLE_MAPS_API_KEY no .env), ative "Maps JavaScript API" e "Geocoding API" no Google Cloud Console.</p></div>';
        }
    }
    function loadScript(callback) {
        if (window.google && window.google.maps) { window[callback](); return; }
        if (!API_KEY || API_KEY.length < 10) {
            showMapError('Chave da API do Google Maps não configurada. Defina GOOGLE_MAPS_API_KEY no .env');
            return;
        }
        var loadTimeout = setTimeout(function() {
            if (getEl(LOADING_ID) && getEl(MAP_CONTAINER_ID) && getEl(MAP_CONTAINER_ID).style.display !== 'block') {
                showMapError('O mapa demorou para carregar. Verifique a chave da API e a conexão.');
            }
        }, 12000);
        var originalCallback = window[callback];
        window[callback] = function() {
            clearTimeout(loadTimeout);
            if (originalCallback) originalCallback();
        };
        var s = document.createElement('script');
        s.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(API_KEY) + '&libraries=places&callback=' + encodeURIComponent(callback);
        s.async = true;
        s.defer = true;
        s.onerror = function() {
            clearTimeout(loadTimeout);
            showMapError('Não foi possível carregar o script do mapa. Verifique sua conexão.');
        };
        document.head.appendChild(s);
    }

    window.initOccurrenceMapWidget = function initOccurrenceMapWidget() {
        try {
            var mapEl = getEl(MAP_CONTAINER_ID);
            var loadingEl = getEl(LOADING_ID);
            if (!mapEl) return;

            var map = new google.maps.Map(mapEl, {
                center: CENTER,
                zoom: ZOOM,
                mapTypeControl: false,
                fullscreenControl: true,
                streetViewControl: true,
                zoomControl: true
            });

            var marker = new google.maps.Marker({
                map: map,
                position: CENTER,
                draggable: true,
                title: 'Arraste para ajustar'
            });

            var hasExistingPosition = false;
            if (getEl('latitude').value && getEl('longitude').value) {
                var lat = parseFloat(getEl('latitude').value);
                var lng = parseFloat(getEl('longitude').value);
                if (!isNaN(lat) && !isNaN(lng)) {
                    var pos = { lat: lat, lng: lng };
                    map.setCenter(pos);
                    marker.setPosition(pos);
                    hasExistingPosition = true;
                }
            }

            var geocoder = new google.maps.Geocoder();

            function showGeocodeStatus(show) {
                var el = getEl(GEOCODE_STATUS_ID);
                if (el) el.style.display = show ? 'block' : 'none';
            }
            function reverseGeocodeAndFill(latLng) {
                setLatLng(latLng.lat(), latLng.lng());
                showGeocodeStatus(true);
                geocoder.geocode({ location: latLng }, function(results, status) {
                    showGeocodeStatus(false);
                    if (status !== 'OK' || !results || !results[0]) return;
                    var place = results[0];
                    place.address_components = place.address_components || [];
                    place.formatted_address = place.formatted_address || '';
                    fillFormFromPlace(place);
                });
            }

            map.addListener('click', function(e) {
                marker.setPosition(e.latLng);
                marker.setVisible(true);
                reverseGeocodeAndFill(e.latLng);
            });

            marker.addListener('dragend', function() {
                reverseGeocodeAndFill(marker.getPosition());
            });

            var locationInput = getInput('location');
            if (locationInput) {
                var autocomplete = new google.maps.places.Autocomplete(locationInput, {
                    fields: ['address_components', 'geometry', 'formatted_address'],
                    types: ['address']
                });
                autocomplete.addListener('place_changed', function() {
                    var place = autocomplete.getPlace();
                    if (!place.geometry) return;
                    map.setCenter(place.geometry.location);
                    marker.setPosition(place.geometry.location);
                    marker.setVisible(true);
                    fillFormFromPlace(place);
                    setLatLng(place.geometry.location.lat(), place.geometry.location.lng());
                });
            }

            function showMapAndHideLoading() {
                mapEl.style.display = 'block';
                if (loadingEl) loadingEl.style.display = 'none';
            }

            if (hasExistingPosition) {
                showMapAndHideLoading();
            } else if (typeof navigator !== 'undefined' && navigator.geolocation) {
                if (loadingEl) loadingEl.innerHTML = '<div class="p-3 text-center text-muted"><p class="mb-0">Obtendo sua localização…</p></div>';
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        var pos = { lat: position.coords.latitude, lng: position.coords.longitude };
                        map.setCenter(pos);
                        marker.setPosition(pos);
                        reverseGeocodeAndFill(new google.maps.LatLng(pos.lat, pos.lng));
                        showMapAndHideLoading();
                    },
                    function() {
                        showMapAndHideLoading();
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
                );
            } else {
                showMapAndHideLoading();
            }
        } catch (e) {
            var loadingEl = getEl(LOADING_ID);
            if (loadingEl) loadingEl.innerHTML = '<div class="p-3 text-center text-danger"><p class="mb-0">Erro ao inicializar o mapa. Verifique a chave da API do Google Maps.</p></div>';
        }
    };

    loadScript('initOccurrenceMapWidget');
})();
</script>

@if (isset($occurrences))
<script>
(function() {
    "use strict";
    var btn = document.getElementById('suggest-drivers-btn');
    var results = document.getElementById('suggest-drivers-results');
    var driverSelect = document.getElementById('driver_id');
    if (!btn || !results || !driverSelect) return;

    var url = '{{ route('occurrences.suggest-drivers', $occurrences->id) }}';

    btn.addEventListener('click', function() {
        results.innerHTML = '<span class="text-muted">Calculando…</span>';
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function(r) { return r.json().then(function(data) { return { status: r.status, data: data }; }); })
            .then(function(res) {
                if (res.status !== 200) {
                    results.innerHTML = '<span class="text-warning">' + (res.data.message || 'Não foi possível calcular.') + '</span>';
                    return;
                }
                var drivers = res.data.drivers || [];
                if (!drivers.length) {
                    results.innerHTML = '<span class="text-muted">Nenhum motorista disponível encontrado.</span>';
                    return;
                }
                results.innerHTML = drivers.map(function(d) {
                    return '<div class="d-flex justify-content-between align-items-center border-bottom py-1">' +
                        '<span>' + d.name + (d.team_name ? ' <span class="text-muted">(' + d.team_name + ')</span>' : '') + ' — ' + d.distance_km + ' km</span>' +
                        '<button type="button" class="btn btn-link btn-sm p-0 suggest-use-btn" data-driver-id="' + d.id + '">Usar</button>' +
                        '</div>';
                }).join('');
                results.querySelectorAll('.suggest-use-btn').forEach(function(useBtn) {
                    useBtn.addEventListener('click', function() {
                        driverSelect.value = useBtn.getAttribute('data-driver-id');
                    });
                });
            })
            .catch(function() {
                results.innerHTML = '<span class="text-danger">Erro ao buscar sugestões.</span>';
            });
    });
})();
</script>
@endif
