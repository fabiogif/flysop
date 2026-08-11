@include('admin.includes.alerts')
@php
    if (!isset($occurrences)) {
        $typeoccurrences_id = old('type_occurrences_id', '');
        $status_occurrences_id = old('status_occurrences_id', '');
        $issuingsId = old('issuings_id', '');
        $driverId = old('driver_id', '');
        $priorityId = old('priority_id', '');
    } else {
        $typeoccurrences_id = old('type_occurrences_id', $occurrences->type_occurrences_id);
        $status_occurrences_id = old('status_occurrences_id', $occurrences->status_occurrences_id);
        $issuingsId = old('issuings_id', $occurrences->issuings_id);
        $driverId = old('driver_id', $occurrences->driver_id ?? '');
        $priorityId = old('priority_id', $occurrences->priority_id ?? '');
    }
    $drivers = $drivers ?? collect();
    $priorities = $priorities ?? collect();
@endphp

<div class="occurrence-form">
    <div class="row">
        <div class="col-lg-7">
            {{-- Solicitante --}}
            <div class="card occurrence-form-section">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-user text-muted mr-1"></i> Solicitante
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="name">Nome completo <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name"
                                    class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                    placeholder="Nome de quem reporta a ocorrência"
                                    value="{{ old('name', $occurrences->name ?? '') }}" required>
                                @error('name') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-md-0">
                                <label for="email">E-mail <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email"
                                    class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                    placeholder="email@exemplo.com"
                                    value="{{ old('email', $occurrences->email ?? '') }}" required>
                                @error('email') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label for="phone">Telefone <span class="text-danger">*</span></label>
                                <input type="tel" name="phone" id="phone"
                                    class="form-control phone {{ $errors->has('phone') ? 'is-invalid' : '' }}"
                                    placeholder="(00) 00000-0000"
                                    value="{{ old('phone', $occurrences->phone ?? '') }}" required>
                                @error('phone') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Classificação e conteúdo --}}
            <div class="card occurrence-form-section">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-clipboard-list text-muted mr-1"></i> Ocorrência
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="title">Título <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="title"
                            class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}"
                            placeholder="Resumo curto da ocorrência"
                            value="{{ old('title', $occurrences->title ?? '') }}" required>
                        @error('title') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>

                    <div class="row">
                        <div class="col-sm-6 col-xl-3">
                            <div class="form-group">
                                <label for="type_occurrences_id">Tipo <span class="text-danger">*</span></label>
                                <select name="type_occurrences_id" id="type_occurrences_id"
                                    class="form-control {{ $errors->has('type_occurrences_id') ? 'is-invalid' : '' }}"
                                    required>
                                    <option value="">Selecione…</option>
                                    @foreach ($typeOccurrences as $typeOccurrence)
                                        <option value="{{ $typeOccurrence->id }}"
                                            {{ (string) $typeOccurrence->id === (string) $typeoccurrences_id ? 'selected' : '' }}>
                                            {{ $typeOccurrence->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type_occurrences_id') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-sm-6 col-xl-3">
                            <div class="form-group">
                                <label for="issuings_id">Órgão <span class="text-danger">*</span></label>
                                <select name="issuings_id" id="issuings_id"
                                    class="form-control {{ $errors->has('issuings_id') ? 'is-invalid' : '' }}"
                                    required>
                                    <option value="">Selecione…</option>
                                    @foreach ($issuings as $issuing)
                                        <option value="{{ $issuing->id }}"
                                            {{ (string) $issuing->id === (string) $issuingsId ? 'selected' : '' }}>
                                            {{ $issuing->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('issuings_id') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-sm-6 col-xl-3">
                            <div class="form-group">
                                <label for="status_occurrences_id">Status <span class="text-danger">*</span></label>
                                <select name="status_occurrences_id" id="status_occurrences_id"
                                    class="form-control {{ $errors->has('status_occurrences_id') ? 'is-invalid' : '' }}"
                                    required>
                                    <option value="">Selecione…</option>
                                    @foreach ($statusOccurrences as $statusOccurrence)
                                        <option value="{{ $statusOccurrence->id }}"
                                            {{ (string) $statusOccurrence->id === (string) $status_occurrences_id ? 'selected' : '' }}>
                                            {{ $statusOccurrence->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status_occurrences_id') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-sm-6 col-xl-3">
                            <div class="form-group">
                                <label for="priority_id">Prioridade</label>
                                <select name="priority_id" id="priority_id" class="form-control">
                                    <option value="">Não definida</option>
                                    @foreach ($priorities as $priority)
                                        <option value="{{ $priority->id }}"
                                            {{ (string) $priority->id === (string) $priorityId ? 'selected' : '' }}>
                                            {{ $priority->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="driver_id">Motorista</label>
                        <div class="d-flex flex-wrap align-items-start">
                            <select name="driver_id" id="driver_id" class="form-control flex-grow-1" style="min-width: 12rem;">
                                <option value="">Nenhum</option>
                                @foreach ($drivers as $driver)
                                    <option value="{{ $driver->id }}"
                                        {{ (string) $driver->id === (string) $driverId ? 'selected' : '' }}>
                                        {{ $driver->name }}
                                    </option>
                                @endforeach
                            </select>
                            @if (isset($occurrences))
                                <button type="button" id="suggest-drivers-btn"
                                    class="btn btn-outline-secondary ml-0 ml-sm-2 mt-2 mt-sm-0">
                                    <i class="fas fa-route"></i> Sugerir mais próximo
                                </button>
                            @endif
                        </div>
                        @if (isset($occurrences))
                            <div id="suggest-drivers-results" class="mt-2 small"></div>
                        @endif
                    </div>

                    <div class="form-group mb-0">
                        <label for="description">Descrição <span class="text-danger">*</span></label>
                        <textarea name="description" id="description" rows="5" required
                            class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}"
                            placeholder="Detalhe o que ocorreu, contexto e observações relevantes">{{ old('description', $occurrences->description ?? '') }}</textarea>
                        @error('description') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            {{-- Localização (campos; mapa ao lado) --}}
            <div class="card occurrence-form-section">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-map-marker-alt text-muted mr-1"></i> Localização
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group position-relative">
                        <label for="location-input">Endereço <span class="text-danger">*</span></label>
                        <input type="text" name="address" id="location-input"
                            class="form-control {{ $errors->has('address') ? 'is-invalid' : '' }}"
                            placeholder="Digite o endereço ou marque no mapa"
                            autocomplete="off" required
                            value="{{ old('address', $occurrences->address ?? '') }}">
                        @error('address') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        <small class="form-text text-muted">Busca por endereço (OpenStreetMap) ou clique/arraste o marcador no mapa.</small>
                        <div id="location-suggestions" class="list-group position-absolute w-100 shadow-sm"
                            style="z-index: 1000; display: none; max-height: 220px; overflow-y: auto;"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="sublocality-input">Bairro</label>
                                <input type="text" name="neighborhood" id="sublocality-input" readonly
                                    class="form-control form-control-sm bg-light"
                                    placeholder="Via mapa"
                                    value="{{ old('neighborhood', $occurrences->neighborhood ?? '') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="locality-input">Cidade</label>
                                <input type="text" name="city" id="locality-input" readonly
                                    class="form-control form-control-sm bg-light"
                                    value="{{ old('city', $occurrences->city ?? '') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="administrative_area_level_1-input">UF</label>
                                <input type="text" name="state" id="administrative_area_level_1-input" readonly
                                    class="form-control form-control-sm bg-light"
                                    value="{{ old('state', $occurrences->state ?? '') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="postal_code-input">CEP</label>
                                <input type="text" name="zip" id="postal_code-input" readonly
                                    class="form-control form-control-sm bg-light"
                                    placeholder="—"
                                    value="{{ old('zip', $occurrences->zip ?? '') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6 col-md-4">
                            <div class="form-group mb-0">
                                <label for="latitude">Latitude</label>
                                <input type="text" name="latitude" id="latitude" readonly
                                    class="form-control form-control-sm bg-light"
                                    placeholder="—"
                                    value="{{ old('latitude', $occurrences->latitude ?? '') }}">
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="form-group mb-0">
                                <label for="longitude">Longitude</label>
                                <input type="text" name="longitude" id="longitude" readonly
                                    class="form-control form-control-sm bg-light"
                                    placeholder="—"
                                    value="{{ old('longitude', $occurrences->longitude ?? '') }}">
                            </div>
                        </div>
                    </div>
                    <input type="hidden" value="Brasil" id="country-input" name="country">
                </div>
            </div>

            {{-- Evidências --}}
            <div class="card occurrence-form-section">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-images text-muted mr-1"></i> Evidências
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group mb-md-0">
                                <label for="anexo">Anexos (imagens)</label>
                                <div class="custom-file">
                                    <input type="file" name="anexo[]" id="anexo" multiple accept="image/*"
                                        class="custom-file-input {{ $errors->has('anexo') || $errors->has('anexo.*') ? 'is-invalid' : '' }}">
                                    <label class="custom-file-label" for="anexo" data-browse="Escolher">
                                        Selecione uma ou mais imagens
                                    </label>
                                </div>
                                <small class="form-text text-muted">Até 5 MB por imagem.</small>
                                @error('anexo.*') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label for="anexo_phase">Fase da evidência</label>
                                <select name="anexo_phase" id="anexo_phase" class="form-control">
                                    <option value="">Não classificada</option>
                                    <option value="antes" {{ old('anexo_phase') === 'antes' ? 'selected' : '' }}>Antes</option>
                                    <option value="depois" {{ old('anexo_phase') === 'depois' ? 'selected' : '' }}>Depois</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="occurrence-form-actions">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Salvar
                </button>
                <a href="{{ route('occurrences.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </div>

        {{-- Mapa --}}
        <div class="col-lg-5">
            <div class="card occurrence-form-map">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-map text-muted mr-1"></i> Mapa
                    </h3>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">
                        Clique no mapa para marcar o ponto ou arraste o marcador. O endereço será preenchido automaticamente.
                    </p>
                    <div id="occurrence-map-container" class="position-relative occurrence-map-frame">
                        <div id="occurrence-map-loading"
                            class="position-absolute w-100 h-100 d-flex align-items-center justify-content-center bg-light border rounded">
                            <div class="text-center">
                                <div class="spinner-border text-primary mb-2" role="status" aria-hidden="true"></div>
                                <p class="mb-0 text-muted">Carregando mapa…</p>
                            </div>
                        </div>
                        <div id="occurrence-map" class="occurrence-map-canvas" style="display: none;"></div>
                        <p id="occurrence-map-geocode-status" class="small text-muted mt-2 mb-0" style="display: none;"
                            role="status" aria-live="polite">Buscando endereço…</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@php
    $initialLat = isset($occurrences) && $occurrences->latitude ? (float) $occurrences->latitude : -12.95307;
    $initialLng = isset($occurrences) && $occurrences->longitude ? (float) $occurrences->longitude : -38.49706;
@endphp
<script>
(function() {
    "use strict";
    var MAP_CONTAINER_ID = 'occurrence-map';
    var LOADING_ID = 'occurrence-map-loading';
    var GEOCODE_STATUS_ID = 'occurrence-map-geocode-status';
    var CENTER = { lat: {{ $initialLat }}, lng: {{ $initialLng }} };
    var ZOOM = 17;

    function getEl(id) { return document.getElementById(id); }
    function getInput(name) {
        var el = getEl(name + '-input');
        return el || getEl(name);
    }

    function setLatLng(lat, lng) {
        var latEl = getEl('latitude');
        var lngEl = getEl('longitude');
        if (latEl) latEl.value = lat;
        if (lngEl) lngEl.value = lng;
    }

    function showGeocodeStatus(show) {
        var el = getEl(GEOCODE_STATUS_ID);
        if (el) el.style.display = show ? 'block' : 'none';
    }

    // Preenche os campos a partir de um resultado do Nominatim (OpenStreetMap) — mesmo
    // formato usado tanto pela busca (search-as-you-type) quanto pelo reverse geocoding.
    function fillFormFromNominatim(result) {
        var addr = result.address || {};
        var locInput = getInput('location');
        if (locInput) {
            var street = [addr.road, addr.house_number].filter(Boolean).join(', ');
            locInput.value = street || result.display_name || locInput.value;
        }
        var localityEl = getInput('locality');
        var stateEl = getInput('administrative_area_level_1');
        var zipEl = getInput('postal_code');
        var sublocalityEl = getInput('sublocality');
        if (localityEl) localityEl.value = addr.city || addr.town || addr.village || addr.municipality || '';
        if (stateEl) stateEl.value = addr.state || '';
        if (zipEl) zipEl.value = addr.postcode || '';
        if (sublocalityEl) sublocalityEl.value = addr.suburb || addr.neighbourhood || '';
        if (getEl('country-input')) getEl('country-input').value = addr.country || 'Brasil';
    }

    // Nominatim (OpenStreetMap) — geocoding gratuito, sem chave/cartão. A política de uso
    // público pede no máx. ~1 requisição/segundo; o debounce da busca (500ms) e o fato de
    // reverse geocoding só disparar em clique/arraste (ação humana, não automática) já
    // respeitam essa faixa. Para volume alto de produção, considerar hospedar uma
    // instância própria do Nominatim.
    function reverseGeocode(lat, lng) {
        showGeocodeStatus(true);
        fetch('https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=' + lat + '&lon=' + lng + '&addressdetails=1')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                showGeocodeStatus(false);
                if (data && !data.error) fillFormFromNominatim(data);
            })
            .catch(function() { showGeocodeStatus(false); });
    }

    function initOccurrenceMapWidget() {
        try {
            var mapEl = getEl(MAP_CONTAINER_ID);
            var loadingEl = getEl(LOADING_ID);
            if (!mapEl || !window.L) return;

            var map = L.map(mapEl, { zoomControl: true }).setView([CENTER.lat, CENTER.lng], ZOOM);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>'
            }).addTo(map);

            var marker = L.marker([CENTER.lat, CENTER.lng], { draggable: true }).addTo(map);

            var hasExistingPosition = false;
            if (getEl('latitude').value && getEl('longitude').value) {
                var lat = parseFloat(getEl('latitude').value);
                var lng = parseFloat(getEl('longitude').value);
                if (!isNaN(lat) && !isNaN(lng)) {
                    map.setView([lat, lng], ZOOM);
                    marker.setLatLng([lat, lng]);
                    hasExistingPosition = true;
                }
            }

            map.on('click', function(e) {
                marker.setLatLng(e.latlng);
                setLatLng(e.latlng.lat, e.latlng.lng);
                reverseGeocode(e.latlng.lat, e.latlng.lng);
            });

            marker.on('dragend', function() {
                var pos = marker.getLatLng();
                setLatLng(pos.lat, pos.lng);
                reverseGeocode(pos.lat, pos.lng);
            });

            // Busca de endereço (equivalente ao Places Autocomplete) via Nominatim /search.
            var locationInput = getInput('location');
            var suggestionsBox = getEl('location-suggestions');
            var searchTimeout = null;

            function clearSuggestions() {
                if (suggestionsBox) { suggestionsBox.innerHTML = ''; suggestionsBox.style.display = 'none'; }
            }

            function selectSuggestion(result) {
                var lat = parseFloat(result.lat), lng = parseFloat(result.lon);
                map.setView([lat, lng], ZOOM);
                marker.setLatLng([lat, lng]);
                setLatLng(lat, lng);
                fillFormFromNominatim(result);
                clearSuggestions();
            }

            function renderSuggestions(results) {
                if (!suggestionsBox) return;
                if (!results || !results.length) { clearSuggestions(); return; }
                suggestionsBox.innerHTML = results.map(function(r, i) {
                    return '<button type="button" class="list-group-item list-group-item-action small" data-idx="' + i + '">' + r.display_name + '</button>';
                }).join('');
                suggestionsBox.style.display = 'block';
                Array.prototype.forEach.call(suggestionsBox.querySelectorAll('[data-idx]'), function(btn) {
                    btn.addEventListener('click', function() {
                        selectSuggestion(results[parseInt(btn.getAttribute('data-idx'), 10)]);
                    });
                });
            }

            if (locationInput) {
                locationInput.addEventListener('input', function() {
                    var q = locationInput.value.trim();
                    clearTimeout(searchTimeout);
                    if (q.length < 4) { clearSuggestions(); return; }
                    searchTimeout = setTimeout(function() {
                        fetch('https://nominatim.openstreetmap.org/search?format=jsonv2&addressdetails=1&countrycodes=br&limit=5&q=' + encodeURIComponent(q))
                            .then(function(r) { return r.json(); })
                            .then(renderSuggestions)
                            .catch(function() {});
                    }, 500);
                });
                document.addEventListener('click', function(e) {
                    if (suggestionsBox && e.target !== locationInput && !suggestionsBox.contains(e.target)) clearSuggestions();
                });
            }

            function showMapAndHideLoading() {
                mapEl.style.display = 'block';
                if (loadingEl) loadingEl.style.display = 'none';
                setTimeout(function() { map.invalidateSize(); }, 0);
            }

            if (hasExistingPosition) {
                showMapAndHideLoading();
            } else if (typeof navigator !== 'undefined' && navigator.geolocation) {
                if (loadingEl) loadingEl.innerHTML = '<div class="p-3 text-center text-muted"><p class="mb-0">Obtendo sua localização…</p></div>';
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        var pos = { lat: position.coords.latitude, lng: position.coords.longitude };
                        map.setView([pos.lat, pos.lng], ZOOM);
                        marker.setLatLng([pos.lat, pos.lng]);
                        setLatLng(pos.lat, pos.lng);
                        reverseGeocode(pos.lat, pos.lng);
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
            var loadingErrEl = getEl(LOADING_ID);
            if (loadingErrEl) loadingErrEl.innerHTML = '<div class="p-3 text-center text-danger"><p class="mb-0">Erro ao inicializar o mapa.</p></div>';
        }
    }

    // Leaflet já vem bundlado em app.js (window.L, ver resources/js/bootstrap.js) — sem
    // script externo para carregar. app.js é um <script> comum (sem defer) no fim do
    // <body>, então esperar o DOMContentLoaded garante que window.L já existe.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initOccurrenceMapWidget);
    } else {
        initOccurrenceMapWidget();
    }

    var anexoInput = getEl('anexo');
    if (anexoInput) {
        anexoInput.addEventListener('change', function() {
            var label = document.querySelector('label.custom-file-label[for="anexo"]');
            if (!label) return;
            var files = anexoInput.files;
            if (!files || !files.length) {
                label.textContent = 'Selecione uma ou mais imagens';
                return;
            }
            label.textContent = files.length === 1 ? files[0].name : (files.length + ' arquivos selecionados');
        });
    }
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
