@extends('adminlte::page')
@section('title', 'Painel de Controle')

@section('content_header')


<h1 class="m-0 text-dark">Painel de Controle
    <a href="{{ route('admin.dashboard.export') }}" class="btn btn-sm btn-outline-secondary ml-2">
        <i class="fas fa-file-csv"></i> Exportar uso
    </a>
</h1>
<div class="row">

    <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $totalUsers }}</h3>
                <p>Usuário</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="/admin/users" class="small-box-footer">Mais informações <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $totalIssuings }}</h3>
                <p>Orgão</p>
            </div>
            <div class="icon">
                <i class="fas fa-tablet"></i>
            </div>
            <a href="/admin/issuings" class="small-box-footer">Mais informações <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $totalTypeOccurrence }}</h3>
                <p>Tipo de Ocorrência</p>
            </div>
            <div class="icon">
                <i class="fas fa-layer-group"></i>
            </div>
            <a href="/admin/typeOccurrences" class="small-box-footer">Mais informações <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $totalOcurrencies }}</h3>
                <p>Ocorrência</p>
            </div>
            <div class="icon">
                <i class="fas fa-tablet"></i>
            </div>
            <a href="/admin/occurrences" class="small-box-footer">Mais informações <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ $occurrencesAbertas }}</h3>
                <p>Abertas</p>
            </div>
            <div class="icon"><i class="fas fa-folder-open"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $occurrencesEmAtendimento }}</h3>
                <p>Em atendimento</p>
            </div>
            <div class="icon"><i class="fas fa-truck-moving"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box" style="background-color: #1e8449; color: #fff;">
            <div class="inner">
                <h3>{{ $occurrencesFinalizadasHoje }}</h3>
                <p>Finalizadas hoje</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $occurrencesSlaEstourado }}</h3>
                <p>SLA estourado <small class="d-block">({{ $occurrencesSlaEmRisco }} em risco)</small></p>
            </div>
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
        </div>
    </div>
</div>

@stop

@section('content')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">Filtros</h3>
            </div>
            <div class="card-body">
                <form id="dashboard-filters" class="form-row align-items-end">
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
                                <option value="{{ $priority->id }}">{{ $priority->name }}</option>
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
                    <div class="form-group col-md-12 mb-0">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter"></i> Aplicar filtros</button>
                        <button type="reset" id="dashboard-filters-clear" class="btn btn-sm btn-outline-secondary">Limpar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Ocorrências recentes</h3>
                <small class="text-muted" id="dashboard-occurrences-updated">Atualização automática a cada 1 min</small>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush" id="dashboard-occurrences-recent">
                    <li class="list-group-item text-center text-muted">Carregando…</li>
                </ul>
            </div>
            <div class="card-footer">
                <a href="{{ route('occurrences.index') }}" class="btn btn-sm btn-outline-primary">Ver todas as
                    ocorrências</a>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h3 class="card-title mb-0">Mapa das ocorrências</h3>
                <div class="d-flex align-items-center">
                    <div class="custom-control custom-switch mr-3">
                        <input type="checkbox" class="custom-control-input" id="dashboard-heatmap-toggle">
                        <label class="custom-control-label small" for="dashboard-heatmap-toggle">Mapa de calor</label>
                    </div>
                    <small class="text-muted">
                        <span class="mr-2"><i class="fas fa-circle text-danger" style="font-size: 0.5rem;"></i>
                            Ocorrência</span>
                        <span><i class="fas fa-circle text-primary" style="font-size: 0.5rem;"></i> Motorista em
                            deslocamento</span>
                    </small>
                </div>
            </div>
            <div class="card-body p-0 position-relative">
                <div id="dashboard-map-loading" class="p-4 text-center text-muted"
                    style="min-height: 320px; display: flex; align-items: center; justify-content: center;">
                    Carregando mapa…
                </div>
                <div id="dashboard-occurrences-map" style="height: 400px; display: none;"></div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title mb-0">Ocorrências por dia (últimos 14 dias)</h3></div>
            <div class="card-body"><canvas id="chart-occurrences-by-day" height="120"></canvas></div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card">
            <div class="card-header"><h3 class="card-title mb-0">Por status</h3></div>
            <div class="card-body"><canvas id="chart-occurrences-by-status" height="160"></canvas></div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card">
            <div class="card-header"><h3 class="card-title mb-0">Por prioridade</h3></div>
            <div class="card-body"><canvas id="chart-occurrences-by-priority" height="160"></canvas></div>
        </div>
    </div>
</div>

<script>window.dashboardChartsData = @json($charts);</script>
<!-- Rastreio Dashboard Vue App pode ser injetado aqui pelo app.js se usarmos um componente para a lista, caso contrario apenas Echo no JS -->
<script>
    (function () {
        const container = document.getElementById('dashboard-occurrences-recent');
        const updatedEl = document.getElementById('dashboard-occurrences-updated');
        const baseUrl = '{{ route("admin.dashboard.occurrences-recent") }}';
        const heatmapUrl = '{{ route("admin.dashboard.occurrences-heatmap") }}';
        const driversUrl = '{{ route("admin.dashboard.drivers-last-positions") }}?minutes=30';
        const interval = 60000;
        var lastOccurrences = [];
        var lastDriverPositions = [];
        var heatmapMode = false;

        function currentFilters() {
            var form = document.getElementById('dashboard-filters');
            var params = new URLSearchParams();
            if (form) {
                new FormData(form).forEach(function (value, key) {
                    if (value) params.append(key, value);
                });
            }
            return params;
        }

        function render(items) {
            if (!container) return;
            if (!items.length) {
                container.innerHTML = '<li class="list-group-item text-center text-muted">Nenhuma ocorrência recente.</li>';
                return;
            }
            container.innerHTML = items.map(function (o) {
                return '<li class="list-group-item d-flex justify-content-between align-items-center">' +
                    '<a href="/admin/occurrences/' + o.id + '">' + (o.title || o.name || 'Ocorrência #' + o.id) + '</a>' +
                    '<span><span class="badge badge-secondary mr-1">' + (o.status || '—') + '</span>' +
                    '<small class="text-muted">' + (o.updated_at_human || '') + '</small></span></li>';
            }).join('');
        }

        function fetchRecent() {
            var params = currentFilters();
            params.set('limit', 10);
            fetch(baseUrl + '?' + params.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    var items = data.occurrences || [];
                    lastOccurrences = items;
                    render(items);
                    if (updatedEl) updatedEl.textContent = 'Atualizado agora';
                    if (typeof window.updateDashboardMap === 'function') window.updateDashboardMap(items);
                })
                .catch(function () {
                    if (container) container.innerHTML = '<li class="list-group-item text-center text-danger">Erro ao carregar.</li>';
                });
        }

        function fetchHeatmap() {
            var params = currentFilters();
            fetch(heatmapUrl + '?' + params.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (typeof window.updateDashboardHeatmap === 'function') window.updateDashboardHeatmap(data.points || []);
                })
                .catch(function () { });
        }

        function fetchDriverPositions() {
            fetch(driversUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    lastDriverPositions = data.drivers || [];
                    if (typeof window.updateDashboardDriverMarkers === 'function') window.updateDashboardDriverMarkers(lastDriverPositions);
                })
                .catch(function () { });
        }

        function refreshAll() {
            fetchRecent();
            fetchDriverPositions();
            if (heatmapMode) fetchHeatmap();
        }

        window.initDashboardMap = function () {
            var mapEl = document.getElementById('dashboard-occurrences-map');
            var loadingEl = document.getElementById('dashboard-map-loading');
            if (!mapEl || !window.L) return;

            var map = L.map(mapEl, { zoomControl: true }).setView([-12.95307, -38.49706], 10);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>'
            }).addTo(map);

            var clusterGroup = L.markerClusterGroup();
            map.addLayer(clusterGroup);
            var driverLayer = L.layerGroup().addTo(map);
            var heatmapLayer = L.heatLayer([], { radius: 30 });

            // Tempo real: escuta o canal de broadcast e corrige só o marcador afetado
            // (window.patchDashboardOccurrence, definida abaixo) — antes disparava um
            // refreshAll() completo a cada evento, mesmo o payload já trazendo os dados
            // prontos para atualizar 1 marcador. Polling continua como fallback (setInterval
            // mais abaixo) para o caso de o Echo cair sem o front perceber.
            if (window.Echo) {
                window.Echo.private('occurrences-dashboard').listen('OccurrenceUpdated', function (e) {
                    if (typeof window.patchDashboardOccurrence === 'function') {
                        window.patchDashboardOccurrence(e);
                    } else {
                        refreshAll();
                    }
                });
            }

            var markersById = {};

            function clearMarkers() {
                clusterGroup.clearLayers();
                markersById = {};
            }

            // Pino SVG colorido pela prioridade, com a inicial do tipo de ocorrência —
            // substitui o ícone estático "mapfiles/ms/icons" (que não diferenciava nada
            // além de sempre desenhar vermelho, por um bug no ternário anterior).
            function occurrenceMarkerIcon(color, typeLabel) {
                var fill = color || '#6c757d';
                var letter = (typeLabel || '?').trim().charAt(0).toUpperCase() || '?';
                var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="28" height="38" viewBox="0 0 28 38">' +
                    '<path d="M14 0C6.3 0 0 6.3 0 14c0 9.8 14 24 14 24s14-14.2 14-24C28 6.3 21.7 0 14 0z" fill="' + fill + '" stroke="rgba(0,0,0,.35)" stroke-width="1"/>' +
                    '<circle cx="14" cy="14" r="9" fill="#fff"/>' +
                    '<text x="14" y="18.5" font-size="11" text-anchor="middle" fill="' + fill + '" font-family="Arial,Helvetica,sans-serif" font-weight="700">' + letter + '</text>' +
                    '</svg>';
                return L.icon({
                    iconUrl: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
                    iconSize: [28, 38],
                    iconAnchor: [14, 38],
                    popupAnchor: [0, -34]
                });
            }
            window.occurrenceMarkerIcon = occurrenceMarkerIcon;

            function occurrenceInfoContent(o) {
                return '<div style="min-width: 180px;">' +
                    '<strong>' + (o.title || o.name || 'Ocorrência #' + o.id) + '</strong>' +
                    '<p class="mb-1 small">' + (o.status || '—') + (o.type ? ' · ' + o.type : '') + (o.priority ? ' · <span style="color:' + (o.priority_color || '#6c757d') + '">' + o.priority + '</span>' : '') + '</p>' +
                    (o.address ? '<p class="mb-1 small text-muted">' + o.address + '</p>' : '') +
                    '<a href="/admin/occurrences/' + o.id + '" class="small">Ver detalhes</a></div>';
            }

            // Cria ou atualiza o marcador de UMA ocorrência (usado tanto no fetch completo
            // quanto na atualização incremental via push — ver window.patchDashboardOccurrence).
            function upsertOccurrenceMarker(o) {
                var lat = o.latitude != null ? parseFloat(o.latitude) : NaN;
                var lng = o.longitude != null ? parseFloat(o.longitude) : NaN;
                var existing = markersById[o.id];

                if (isNaN(lat) || isNaN(lng)) {
                    if (existing) {
                        clusterGroup.removeLayer(existing);
                        delete markersById[o.id];
                    }
                    return { marker: null, isNew: false };
                }

                var icon = occurrenceMarkerIcon(o.priority_color, o.type);
                var title = o.title || o.name || 'Ocorrência #' + o.id;

                if (existing) {
                    existing.setLatLng([lat, lng]);
                    existing.setIcon(icon);
                    if (existing.getPopup()) existing.getPopup().setContent(occurrenceInfoContent(o));
                    return { marker: existing, isNew: false };
                }

                var marker = L.marker([lat, lng], { icon: icon, title: title });
                marker.bindPopup(occurrenceInfoContent(o));
                markersById[o.id] = marker;

                return { marker: marker, isNew: true };
            }
            window.upsertOccurrenceMarker = function (o) { return upsertOccurrenceMarker(o).marker; };

            // Atualização incremental (push): corrige/insere 1 marcador sem refazer o fetch
            // inteiro nem redesenhar o mapa — substitui o antigo refreshAll() no listener do
            // Echo. lastOccurrences (lista lateral) também é corrigida no mesmo passo. Só
            // marcadores GENUINAMENTE novos precisam ser anexados ao cluster aqui — um
            // existente já está visível, só foi reposicionado/recolorido no lugar.
            window.patchDashboardOccurrence = function (o) {
                var result = upsertOccurrenceMarker(o);
                if (result.marker && result.isNew && !heatmapMode) {
                    clusterGroup.addLayer(result.marker);
                }
                var idx = lastOccurrences.findIndex(function (item) { return item.id === o.id; });
                if (idx >= 0) lastOccurrences[idx] = o; else lastOccurrences.unshift(o);
                if (typeof render === 'function') render(lastOccurrences.slice(0, 10));
                if (heatmapMode) fetchHeatmap();
            };

            window.updateDashboardMap = function (occurrences) {
                clearMarkers();
                var latlngs = [];
                (occurrences || []).forEach(function (o) {
                    var result = upsertOccurrenceMarker(o);
                    if (!result.marker) return;
                    latlngs.push(result.marker.getLatLng());
                });

                if (!heatmapMode) {
                    Object.keys(markersById).forEach(function (id) { clusterGroup.addLayer(markersById[id]); });
                }

                if (latlngs.length > 0) {
                    map.fitBounds(L.latLngBounds(latlngs), { padding: [60, 60], maxZoom: 16 });
                }
                mapEl.style.display = 'block';
                if (loadingEl) loadingEl.style.display = 'none';
                setTimeout(function () { map.invalidateSize(); }, 0);
            };

            window.updateDashboardHeatmap = function (points) {
                var latlngs = (points || []).map(function (p) { return [p.lat, p.lng]; });
                heatmapLayer.setLatLngs(latlngs);
            };

            var heatmapToggle = document.getElementById('dashboard-heatmap-toggle');
            if (heatmapToggle) {
                heatmapToggle.addEventListener('change', function () {
                    heatmapMode = heatmapToggle.checked;
                    if (heatmapMode) {
                        map.removeLayer(clusterGroup);
                        heatmapLayer.addTo(map);
                        fetchHeatmap();
                    } else {
                        map.removeLayer(heatmapLayer);
                        map.addLayer(clusterGroup);
                        window.updateDashboardMap(lastOccurrences);
                    }
                });
            }

            window.updateDashboardDriverMarkers = function (drivers) {
                driverLayer.clearLayers();
                (drivers || []).forEach(function (d) {
                    var lat = d.latitude != null ? parseFloat(d.latitude) : NaN;
                    var lng = d.longitude != null ? parseFloat(d.longitude) : NaN;
                    if (isNaN(lat) || isNaN(lng)) return;
                    var marker = L.circleMarker([lat, lng], {
                        radius: 8, color: '#fff', weight: 2, fillColor: '#007bff', fillOpacity: 1
                    });
                    var content = '<div style="min-width: 180px;">' +
                        '<strong><i class="fas fa-car text-primary"></i> ' + (d.driver_name || 'Motorista') + '</strong>' +
                        '<p class="mb-1 small">Em deslocamento</p>' +
                        (d.occurrence_id ? '<a href="/admin/occurrences/' + d.occurrence_id + '" class="small">Ver ocorrência #' + d.occurrence_id + '</a>' : '') + '</div>';
                    marker.bindPopup(content);
                    driverLayer.addLayer(marker);
                });
            };

            if (lastOccurrences.length) window.updateDashboardMap(lastOccurrences);
            else { mapEl.style.display = 'block'; if (loadingEl) loadingEl.style.display = 'none'; }
            if (lastDriverPositions.length && typeof window.updateDashboardDriverMarkers === 'function') window.updateDashboardDriverMarkers(lastDriverPositions);
        };

        var filtersForm = document.getElementById('dashboard-filters');
        if (filtersForm) {
            filtersForm.addEventListener('submit', function (e) {
                e.preventDefault();
                refreshAll();
            });
        }
        var clearBtn = document.getElementById('dashboard-filters-clear');
        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                setTimeout(refreshAll, 0);
            });
        }

        refreshAll();
        setInterval(refreshAll, interval);

        // Leaflet já vem bundlado em app.js (window.L, ver resources/js/bootstrap.js) —
        // sem script externo para carregar. app.js é um <script> comum (sem defer) no fim
        // do <body>, então esperar o DOMContentLoaded garante que window.L já existe.
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', window.initDashboardMap);
        } else {
            window.initDashboardMap();
        }
    })();
</script>
@stop
