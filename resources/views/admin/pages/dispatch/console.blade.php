@extends('adminlte::page')

@section('title', 'Central de Despacho')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => 'Central de Despacho',
        'subtitle' => 'Lista e mapa lado a lado — triagem, despacho e acompanhamento em um único lugar.',
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Central de Despacho'],
        ],
    ])
@stop

@section('content')
    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="dispatch-chips mb-2">
                <button type="button" class="btn btn-sm btn-outline-secondary dispatch-chip active" data-chip="all">Todas</button>
                <button type="button" class="btn btn-sm btn-outline-danger dispatch-chip" data-chip="critical">Críticas</button>
                <button type="button" class="btn btn-sm btn-outline-secondary dispatch-chip" data-chip="last24h">Últimas 24h</button>
                <button type="button" class="btn btn-sm btn-outline-secondary dispatch-chip" data-chip="open">Em aberto</button>
                <button type="button" class="btn btn-sm btn-outline-warning dispatch-chip" data-chip="no_driver">Sem atendimento</button>
                <button type="button" class="btn btn-sm btn-outline-primary dispatch-chip" data-chip="nearby">Próximas</button>
            </div>
            <form id="dispatch-filters" class="form-row align-items-end">
                <div class="form-group col-md-2">
                    <label class="small mb-1">Status</label>
                    <select name="status_occurrences_id" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach ($filterOptions['statusOccurrences'] as $status)
                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label class="small mb-1">Tipo</label>
                    <select name="type_occurrences_id" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach ($filterOptions['typeOccurrences'] as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label class="small mb-1">Prioridade</label>
                    <select name="priority_id" class="form-control form-control-sm">
                        <option value="">Todas</option>
                        @foreach ($filterOptions['priorities'] as $priority)
                            <option value="{{ $priority->id }}" data-weight="{{ $priority->weight }}">{{ $priority->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label class="small mb-1">Motorista</label>
                    <select name="driver_id" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach ($filterOptions['drivers'] as $driver)
                            <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label class="small mb-1">De</label>
                    <input type="date" name="date_from" class="form-control form-control-sm">
                </div>
                <div class="form-group col-md-2">
                    <label class="small mb-1">Até</label>
                    <input type="date" name="date_to" class="form-control form-control-sm">
                </div>
                <input type="hidden" name="open_only" value="">
                <input type="hidden" name="no_driver" value="">
                <div class="form-group col-md-12 mb-0">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter"></i> Aplicar filtros</button>
                    <button type="reset" id="dispatch-filters-clear" class="btn btn-sm btn-outline-secondary">Limpar tudo</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Ocorrências <span id="dispatch-count" class="badge badge-secondary ml-1">0</span></h3>
                    <small class="text-muted" id="dispatch-updated"></small>
                </div>
                <div class="card-body p-0" style="max-height: 640px; overflow-y: auto;">
                    <ul class="list-group list-group-flush" id="dispatch-list">
                        <li class="list-group-item text-center text-muted">Carregando…</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body p-0 position-relative">
                    <div id="dispatch-map-loading" class="p-4 text-center text-muted"
                        style="min-height: 640px; display: flex; align-items: center; justify-content: center;">
                        Carregando mapa…
                    </div>
                    <div id="dispatch-map" style="height: 640px; display: none;"></div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .dispatch-chips .dispatch-chip.active { font-weight: 600; }
        .dispatch-item { cursor: pointer; }
        .dispatch-item.is-selected { background: rgba(0, 123, 255, .06); }
        .dispatch-item .dispatch-item-title { font-weight: 600; }
        .dispatch-candidates { border-top: 1px dashed #dee2e6; margin-top: 8px; padding-top: 8px; }
        .dispatch-candidate-row { display: flex; align-items: center; justify-content: space-between; padding: 4px 0; font-size: 12.5px; }
    </style>

<script>
(function () {
    "use strict";

    var recentUrl = '{{ route("admin.dashboard.occurrences-recent") }}';
    var csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '';
    var API_KEY = @json(config('services.google.maps_key') ?: env('GOOGLE_MAPS_API_KEY', ''));
    var criticalPriorityId = '';
    (function pickCriticalPriority() {
        var best = null;
        document.querySelectorAll('#dispatch-filters select[name="priority_id"] option[data-weight]').forEach(function (opt) {
            var w = parseInt(opt.getAttribute('data-weight'), 10);
            if (!best || w > best.weight) best = { id: opt.value, weight: w };
        });
        if (best) criticalPriorityId = best.id;
    })();

    var listEl = document.getElementById('dispatch-list');
    var countEl = document.getElementById('dispatch-count');
    var updatedEl = document.getElementById('dispatch-updated');
    var filtersForm = document.getElementById('dispatch-filters');
    var lastItems = [];
    var selectedId = null;

    var map, markersById = {}, occurrenceMarkerIconFn;
    var dispatchOverlay = { markers: [], lines: [] };

    function clearDispatchOverlay() {
        dispatchOverlay.markers.forEach(function (m) { m.setMap(null); });
        dispatchOverlay.lines.forEach(function (l) { l.setMap(null); });
        dispatchOverlay = { markers: [], lines: [] };
    }

    function candidateMarkerIcon() {
        var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22">' +
            '<circle cx="11" cy="11" r="9" fill="#28a745" stroke="#fff" stroke-width="2"/>' +
            '<path d="M6 11l3 3 7-7" stroke="#fff" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>' +
            '</svg>';
        return {
            url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
            scaledSize: new google.maps.Size(22, 22),
            anchor: new google.maps.Point(11, 11)
        };
    }

    function currentFilters() {
        var params = new URLSearchParams();
        new FormData(filtersForm).forEach(function (value, key) {
            if (value) params.append(key, value);
        });
        params.set('limit', 100);
        return params;
    }

    function statusBadgeClass(status) {
        if (!status) return 'badge-secondary';
        var s = status.toLowerCase();
        if (s.indexOf('finaliz') >= 0 || s.indexOf('cancel') >= 0) return 'badge-secondary';
        if (s.indexOf('atend') >= 0 || s.indexOf('desloc') >= 0) return 'badge-info';
        if (s.indexOf('aceit') >= 0) return 'badge-primary';
        return 'badge-warning';
    }

    function renderList() {
        if (!listEl) return;
        countEl.textContent = lastItems.length;
        if (!lastItems.length) {
            listEl.innerHTML = '<li class="list-group-item text-center text-muted">Nenhuma ocorrência para os filtros atuais.</li>';
            return;
        }
        listEl.innerHTML = lastItems.map(function (o) {
            var sel = (o.id === selectedId) ? ' is-selected' : '';
            return '<li class="list-group-item dispatch-item' + sel + '" data-id="' + o.id + '">' +
                '<div class="d-flex justify-content-between align-items-start">' +
                '<div>' +
                '<div class="dispatch-item-title">' + (o.title || o.name || 'Ocorrência #' + o.id) + '</div>' +
                '<div class="small text-muted">' + (o.protocol || '') + (o.address ? ' · ' + o.address : '') + '</div>' +
                '</div>' +
                '<div class="text-right"><span class="badge ' + statusBadgeClass(o.status) + '">' + (o.status || '—') + '</span>' +
                (o.priority ? '<div class="small mt-1" style="color:' + (o.priority_color || '#6c757d') + '">' + o.priority + '</div>' : '') +
                '</div></div>' +
                '<div class="mt-2">' +
                '<a href="/admin/occurrences/' + o.id + '" class="btn btn-xs btn-outline-secondary btn-sm">Ver ficha</a> ' +
                ((o.latitude != null && o.longitude != null) ? '<button type="button" class="btn btn-xs btn-sm btn-outline-success dispatch-btn" data-id="' + o.id + '">Despachar</button>' : '<span class="small text-muted ml-1">Sem localização</span>') +
                '</div>' +
                '<div class="dispatch-candidates" id="dispatch-candidates-' + o.id + '" style="display:none;"></div>' +
                '</li>';
        }).join('');
    }

    function fetchRecent() {
        var params = currentFilters();
        fetch(recentUrl + '?' + params.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                lastItems = data.occurrences || [];
                renderList();
                if (updatedEl) updatedEl.textContent = 'Atualizado agora';
                if (typeof window.dispatchUpdateMap === 'function') window.dispatchUpdateMap(lastItems);
            })
            .catch(function () {
                listEl.innerHTML = '<li class="list-group-item text-center text-danger">Erro ao carregar.</li>';
            });
    }

    // ---- Mapa ----
    function loadMapScript(callback) {
        if (window.google && window.google.maps) { window[callback](); return; }
        if (!API_KEY || API_KEY.length < 10) {
            var loadingEl = document.getElementById('dispatch-map-loading');
            if (loadingEl) loadingEl.innerHTML = '<p class="text-warning mb-0">Chave do Google Maps não configurada. Defina GOOGLE_MAPS_API_KEY no .env.</p>';
            return;
        }
        var s = document.createElement('script');
        s.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(API_KEY) + '&callback=' + encodeURIComponent(callback);
        s.async = true;
        s.defer = true;
        document.head.appendChild(s);
    }

    window.initDispatchMap = function () {
        var mapEl = document.getElementById('dispatch-map');
        var loadingEl = document.getElementById('dispatch-map-loading');
        if (!mapEl) return;

        map = new google.maps.Map(mapEl, {
            center: { lat: -12.95307, lng: -38.49706 },
            zoom: 11,
            fullscreenControl: true,
            streetViewControl: false
        });

        // Mesmo pino SVG (cor por prioridade, letra por tipo) usado no dashboard —
        // reimplementado aqui porque este é um bundle de página separado (padrão já
        // usado no projeto: JS de mapa inline por página, sem módulo compartilhado).
        occurrenceMarkerIconFn = function (color, typeLabel) {
            var fill = color || '#6c757d';
            var letter = (typeLabel || '?').trim().charAt(0).toUpperCase() || '?';
            var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="28" height="38" viewBox="0 0 28 38">' +
                '<path d="M14 0C6.3 0 0 6.3 0 14c0 9.8 14 24 14 24s14-14.2 14-24C28 6.3 21.7 0 14 0z" fill="' + fill + '" stroke="rgba(0,0,0,.35)" stroke-width="1"/>' +
                '<circle cx="14" cy="14" r="9" fill="#fff"/>' +
                '<text x="14" y="18.5" font-size="11" text-anchor="middle" fill="' + fill + '" font-family="Arial,Helvetica,sans-serif" font-weight="700">' + letter + '</text>' +
                '</svg>';
            return {
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
                scaledSize: new google.maps.Size(28, 38),
                anchor: new google.maps.Point(14, 38)
            };
        };

        function focusMarker(id) {
            var m = markersById[id];
            if (!m) return;
            map.panTo(m.getPosition());
            if (map.getZoom() < 15) map.setZoom(15);
            if (m.__infowindow) m.__infowindow.open(map, m);
        }
        window.dispatchFocusMarker = focusMarker;

        window.dispatchUpdateMap = function (items) {
            Object.keys(markersById).forEach(function (id) { markersById[id].setMap(null); });
            markersById = {};
            var bounds = null;
            (items || []).forEach(function (o) {
                var lat = o.latitude != null ? parseFloat(o.latitude) : NaN;
                var lng = o.longitude != null ? parseFloat(o.longitude) : NaN;
                if (isNaN(lat) || isNaN(lng)) return;
                var pos = { lat: lat, lng: lng };
                var marker = new google.maps.Marker({
                    map: map,
                    position: pos,
                    title: o.title || o.name || 'Ocorrência #' + o.id,
                    icon: occurrenceMarkerIconFn(o.priority_color, o.type)
                });
                var content = '<div class="p-2" style="min-width: 180px;">' +
                    '<strong>' + (o.title || o.name || 'Ocorrência #' + o.id) + '</strong>' +
                    '<p class="mb-1 small">' + (o.status || '—') + (o.type ? ' · ' + o.type : '') + '</p>' +
                    (o.address ? '<p class="mb-1 small text-muted">' + o.address + '</p>' : '') +
                    '<a href="/admin/occurrences/' + o.id + '" class="small">Ver detalhes</a></div>';
                var infowindow = new google.maps.InfoWindow({ content: content });
                marker.__infowindow = infowindow;
                marker.addListener('click', function () { infowindow.open(map, marker); selectItem(o.id); });
                markersById[o.id] = marker;
                if (!bounds) bounds = new google.maps.LatLngBounds();
                bounds.extend(pos);
            });
            if (bounds) map.fitBounds(bounds, 60);
            mapEl.style.display = 'block';
            if (loadingEl) loadingEl.style.display = 'none';
        };

        // Tempo real: mesmo canal/evento do dashboard, mesma lógica de patch incremental.
        if (window.Echo) {
            window.Echo.private('occurrences-dashboard').listen('OccurrenceUpdated', function (o) {
                var lat = o.latitude != null ? parseFloat(o.latitude) : NaN;
                var lng = o.longitude != null ? parseFloat(o.longitude) : NaN;
                if (!isNaN(lat) && !isNaN(lng)) {
                    var pos = { lat: lat, lng: lng };
                    var existing = markersById[o.id];
                    if (existing) {
                        existing.setPosition(pos);
                        existing.setIcon(occurrenceMarkerIconFn(o.priority_color, o.type));
                    } else {
                        var marker = new google.maps.Marker({ map: map, position: pos, title: o.title || 'Ocorrência #' + o.id, icon: occurrenceMarkerIconFn(o.priority_color, o.type) });
                        marker.addListener('click', function () { selectItem(o.id); });
                        markersById[o.id] = marker;
                    }
                }
                var idx = lastItems.findIndex(function (i) { return i.id === o.id; });
                if (idx >= 0) lastItems[idx] = o; else lastItems.unshift(o);
                renderList();
            });
        }

        mapEl.style.display = 'block';
        if (loadingEl) loadingEl.style.display = 'none';
        fetchRecent();
    };

    function selectItem(id) {
        selectedId = id;
        renderList();
        if (typeof window.dispatchFocusMarker === 'function') window.dispatchFocusMarker(id);
    }

    // ---- Despacho visual (sugestão + atribuição) ----
    function drawDispatchCandidates(occurrence, drivers) {
        clearDispatchOverlay();
        var occPos = { lat: parseFloat(occurrence.latitude), lng: parseFloat(occurrence.longitude) };
        drivers.forEach(function (d) {
            if (d.latitude == null || d.longitude == null) return;
            var pos = { lat: parseFloat(d.latitude), lng: parseFloat(d.longitude) };
            var marker = new google.maps.Marker({ map: map, position: pos, title: d.name + ' — ' + d.distance_km + ' km', icon: candidateMarkerIcon() });
            var line = new google.maps.Polyline({
                map: map,
                path: [occPos, pos],
                strokeOpacity: 0,
                icons: [{ icon: { path: 'M 0,-1 0,1', strokeOpacity: .7, scale: 3 }, offset: '0', repeat: '10px' }]
            });
            dispatchOverlay.markers.push(marker);
            dispatchOverlay.lines.push(line);
        });
        var bounds = new google.maps.LatLngBounds();
        bounds.extend(occPos);
        drivers.forEach(function (d) { if (d.latitude != null) bounds.extend({ lat: parseFloat(d.latitude), lng: parseFloat(d.longitude) }); });
        map.fitBounds(bounds, 80);
    }

    function renderCandidates(occurrenceId, drivers) {
        var box = document.getElementById('dispatch-candidates-' + occurrenceId);
        if (!box) return;
        if (!drivers.length) {
            box.innerHTML = '<p class="small text-muted mb-0">Nenhum motorista disponível encontrado.</p>';
        } else {
            box.innerHTML = drivers.map(function (d) {
                return '<div class="dispatch-candidate-row">' +
                    '<span>' + d.name + (d.team_name ? ' <span class="text-muted">(' + d.team_name + ')</span>' : '') + ' — ' + d.distance_km + ' km</span>' +
                    '<button type="button" class="btn btn-xs btn-sm btn-primary dispatch-assign-btn" data-occurrence="' + occurrenceId + '" data-driver="' + d.id + '">Atribuir</button>' +
                    '</div>';
            }).join('');
        }
        box.style.display = 'block';
    }

    document.addEventListener('click', function (e) {
        var item = e.target.closest('.dispatch-item');
        if (item && !e.target.closest('button') && !e.target.closest('a')) {
            selectItem(parseInt(item.getAttribute('data-id'), 10));
            return;
        }

        var dispatchBtn = e.target.closest('.dispatch-btn');
        if (dispatchBtn) {
            var occId = parseInt(dispatchBtn.getAttribute('data-id'), 10);
            var occurrence = lastItems.find(function (o) { return o.id === occId; });
            dispatchBtn.disabled = true;
            dispatchBtn.textContent = 'Buscando…';
            fetch('/admin/occurrences/' + occId + '/suggest-drivers', { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
                .then(function (result) {
                    dispatchBtn.disabled = false;
                    dispatchBtn.textContent = 'Despachar';
                    if (!result.ok) {
                        alert(result.data.message || 'Não foi possível sugerir motoristas.');
                        return;
                    }
                    var drivers = result.data.drivers || [];
                    renderCandidates(occId, drivers);
                    if (occurrence) drawDispatchCandidates(occurrence, drivers);
                })
                .catch(function () {
                    dispatchBtn.disabled = false;
                    dispatchBtn.textContent = 'Despachar';
                });
            return;
        }

        var assignBtn = e.target.closest('.dispatch-assign-btn');
        if (assignBtn) {
            var oId = assignBtn.getAttribute('data-occurrence');
            var dId = assignBtn.getAttribute('data-driver');
            assignBtn.disabled = true;
            assignBtn.textContent = 'Atribuindo…';
            fetch('/admin/occurrences/' + oId + '/assign-driver', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ driver_id: dId })
            })
                .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
                .then(function (result) {
                    if (!result.ok) {
                        alert(result.data.message || 'Não foi possível atribuir o motorista.');
                        assignBtn.disabled = false;
                        assignBtn.textContent = 'Atribuir';
                        return;
                    }
                    clearDispatchOverlay();
                    var box = document.getElementById('dispatch-candidates-' + oId);
                    if (box) { box.style.display = 'none'; box.innerHTML = ''; }
                    fetchRecent();
                })
                .catch(function () {
                    assignBtn.disabled = false;
                    assignBtn.textContent = 'Atribuir';
                });
        }
    });

    // ---- Chips de filtro rápido ----
    function setChipActive(chip) {
        document.querySelectorAll('.dispatch-chip').forEach(function (c) { c.classList.remove('active'); });
        chip.classList.add('active');
    }

    function sortByDistanceFromUser(lat, lng) {
        function haversine(lat1, lng1, lat2, lng2) {
            var R = 6371, dLat = (lat2 - lat1) * Math.PI / 180, dLng = (lng2 - lng1) * Math.PI / 180;
            var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLng / 2) * Math.sin(dLng / 2);
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }
        lastItems = lastItems.filter(function (o) { return o.latitude != null && o.longitude != null; })
            .sort(function (a, b) {
                return haversine(lat, lng, parseFloat(a.latitude), parseFloat(a.longitude)) - haversine(lat, lng, parseFloat(b.latitude), parseFloat(b.longitude));
            });
        renderList();
        if (map) map.setCenter({ lat: lat, lng: lng });
    }

    document.querySelectorAll('.dispatch-chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            setChipActive(chip);
            var kind = chip.getAttribute('data-chip');
            filtersForm.reset();
            filtersForm.querySelector('[name="open_only"]').value = '';
            filtersForm.querySelector('[name="no_driver"]').value = '';

            if (kind === 'critical' && criticalPriorityId) {
                filtersForm.querySelector('[name="priority_id"]').value = criticalPriorityId;
            } else if (kind === 'last24h') {
                var d = new Date(); d.setDate(d.getDate() - 1);
                filtersForm.querySelector('[name="date_from"]').value = d.toISOString().slice(0, 10);
            } else if (kind === 'open') {
                filtersForm.querySelector('[name="open_only"]').value = '1';
            } else if (kind === 'no_driver') {
                filtersForm.querySelector('[name="no_driver"]').value = '1';
            } else if (kind === 'nearby') {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function (pos) {
                        sortByDistanceFromUser(pos.coords.latitude, pos.coords.longitude);
                    }, function () { /* permissão negada: mantém ordenação atual */ });
                }
            }
            fetchRecent();
        });
    });

    filtersForm.addEventListener('submit', function (e) {
        e.preventDefault();
        setChipActive(document.querySelector('.dispatch-chip[data-chip="all"]'));
        fetchRecent();
    });
    document.getElementById('dispatch-filters-clear').addEventListener('click', function () {
        setTimeout(function () { setChipActive(document.querySelector('.dispatch-chip[data-chip="all"]')); fetchRecent(); }, 0);
    });

    loadMapScript('initDispatchMap');
    setInterval(fetchRecent, 60000);
})();
</script>
@stop
