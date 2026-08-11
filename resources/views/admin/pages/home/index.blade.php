@extends('adminlte::page')
@section('title', 'Painel de Controle')

@section('content_header')
    @php
        ob_start();
    @endphp
    <a href="{{ route('admin.dashboard.export') }}" class="btn btn-sm btn-outline-secondary ciop-dash-export">
        <i class="fas fa-file-csv" aria-hidden="true"></i>
        <span>Exportar uso</span>
    </a>
    @php
        $actionsHtml = ob_get_clean();
    @endphp

    @include('admin.includes.page-header', [
        'title' => 'Painel de Controle',
        'subtitle' => 'Visão operacional da central — atualização contínua do mapa e das ocorrências.',
        'breadcrumbs' => [
            ['label' => 'Painel de Controle'],
        ],
        'actionsHtml' => $actionsHtml,
    ])
@stop

@section('content')
<div class="ciop-dashboard">

    <section class="ciop-dash-section" aria-labelledby="dash-kpis-title">
        <div class="ciop-dash-section-head">
            <h2 id="dash-kpis-title" class="ciop-dash-section-title">Indicadores</h2>
            <p class="ciop-dash-section-desc">Cadastros e status operacional em tempo quase real.</p>
        </div>

        <div class="ciop-dash-kpi-groups">
            <div class="ciop-dash-kpi-group">
                <p class="ciop-dash-kpi-group-label">Cadastros</p>
                <div class="ciop-dash-stats ciop-dash-stats-4">
                    <a href="{{ url('/admin/users') }}" class="ciop-dash-stat tone-users">
                        <span class="ciop-dash-stat-icon" aria-hidden="true"><i class="fas fa-users"></i></span>
                        <div class="ciop-dash-stat-body">
                            <p class="ciop-dash-stat-label">Usuários</p>
                            <p class="ciop-dash-stat-value">{{ $totalUsers }}</p>
                        </div>
                        <span class="ciop-dash-stat-link">Abrir <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
                    </a>

                    <a href="{{ url('/admin/issuings') }}" class="ciop-dash-stat tone-org">
                        <span class="ciop-dash-stat-icon" aria-hidden="true"><i class="fas fa-building"></i></span>
                        <div class="ciop-dash-stat-body">
                            <p class="ciop-dash-stat-label">Órgãos</p>
                            <p class="ciop-dash-stat-value">{{ $totalIssuings }}</p>
                        </div>
                        <span class="ciop-dash-stat-link">Abrir <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
                    </a>

                    <a href="{{ url('/admin/typeOccurrences') }}" class="ciop-dash-stat tone-type">
                        <span class="ciop-dash-stat-icon" aria-hidden="true"><i class="fas fa-layer-group"></i></span>
                        <div class="ciop-dash-stat-body">
                            <p class="ciop-dash-stat-label">Tipos</p>
                            <p class="ciop-dash-stat-value">{{ $totalTypeOccurrence }}</p>
                        </div>
                        <span class="ciop-dash-stat-link">Abrir <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
                    </a>

                    <a href="{{ url('/admin/occurrences') }}" class="ciop-dash-stat tone-occ">
                        <span class="ciop-dash-stat-icon" aria-hidden="true"><i class="fas fa-clipboard-list"></i></span>
                        <div class="ciop-dash-stat-body">
                            <p class="ciop-dash-stat-label">Ocorrências</p>
                            <p class="ciop-dash-stat-value">{{ $totalOcurrencies }}</p>
                        </div>
                        <span class="ciop-dash-stat-link">Abrir <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
                    </a>
                </div>
            </div>

            <div class="ciop-dash-kpi-group">
                <p class="ciop-dash-kpi-group-label">Operação</p>
                <div class="ciop-dash-stats ciop-dash-stats-4">
                    <div class="ciop-dash-stat tone-open">
                        <span class="ciop-dash-stat-icon" aria-hidden="true"><i class="fas fa-folder-open"></i></span>
                        <div class="ciop-dash-stat-body">
                            <p class="ciop-dash-stat-label">Abertas</p>
                            <p class="ciop-dash-stat-value">{{ $occurrencesAbertas }}</p>
                        </div>
                    </div>

                    <div class="ciop-dash-stat tone-progress">
                        <span class="ciop-dash-stat-icon" aria-hidden="true"><i class="fas fa-truck-moving"></i></span>
                        <div class="ciop-dash-stat-body">
                            <p class="ciop-dash-stat-label">Em atendimento</p>
                            <p class="ciop-dash-stat-value">{{ $occurrencesEmAtendimento }}</p>
                        </div>
                    </div>

                    <div class="ciop-dash-stat tone-done">
                        <span class="ciop-dash-stat-icon" aria-hidden="true"><i class="fas fa-check-circle"></i></span>
                        <div class="ciop-dash-stat-body">
                            <p class="ciop-dash-stat-label">Finalizadas hoje</p>
                            <p class="ciop-dash-stat-value">{{ $occurrencesFinalizadasHoje }}</p>
                        </div>
                    </div>

                    <div class="ciop-dash-stat tone-sla {{ $occurrencesSlaEstourado > 0 ? 'is-alert' : '' }}">
                        <span class="ciop-dash-stat-icon" aria-hidden="true"><i class="fas fa-exclamation-triangle"></i></span>
                        <div class="ciop-dash-stat-body">
                            <p class="ciop-dash-stat-label">SLA estourado</p>
                            <p class="ciop-dash-stat-value">{{ $occurrencesSlaEstourado }}</p>
                            <p class="ciop-dash-stat-hint">{{ $occurrencesSlaEmRisco }} em risco</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="ciop-dash-section" aria-labelledby="dash-filters-title">
        <div class="ciop-dash-panel card">
            <div class="card-header ciop-dash-card-head">
                <div>
                    <h2 id="dash-filters-title" class="card-title mb-0">Filtros</h2>
                    <p class="ciop-dash-card-sub">Refine mapa, lista e visão operacional.</p>
                </div>
            </div>
            <div class="card-body">
                <form id="dashboard-filters" class="ciop-dash-filters">
                    <div class="ciop-dash-filters-grid">
                        <div class="form-group mb-0">
                            <label for="dash-filter-status">Status</label>
                            <select id="dash-filter-status" name="status_occurrences_id" class="form-control">
                                <option value="">Todos</option>
                                @foreach ($filterOptions['statusOccurrences'] as $status)
                                    <option value="{{ $status->id }}">{{ $status->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label for="dash-filter-type">Tipo</label>
                            <select id="dash-filter-type" name="type_occurrences_id" class="form-control">
                                <option value="">Todos</option>
                                @foreach ($filterOptions['typeOccurrences'] as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label for="dash-filter-priority">Prioridade</label>
                            <select id="dash-filter-priority" name="priority_id" class="form-control">
                                <option value="">Todas</option>
                                @foreach ($filterOptions['priorities'] as $priority)
                                    <option value="{{ $priority->id }}">{{ $priority->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label for="dash-filter-driver">Motorista</label>
                            <select id="dash-filter-driver" name="driver_id" class="form-control">
                                <option value="">Todos</option>
                                @foreach ($filterOptions['drivers'] as $driver)
                                    <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label for="dash-filter-from">De</label>
                            <input id="dash-filter-from" type="date" name="date_from" class="form-control">
                        </div>
                        <div class="form-group mb-0">
                            <label for="dash-filter-to">Até</label>
                            <input id="dash-filter-to" type="date" name="date_to" class="form-control">
                        </div>
                    </div>
                    <div class="ciop-dash-filters-actions">
                        <button type="submit" class="btn ciop-btn-panel">
                            <i class="fas fa-filter" aria-hidden="true"></i> Aplicar filtros
                        </button>
                        <button type="reset" id="dashboard-filters-clear" class="btn btn-outline-secondary">
                            Limpar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <section class="ciop-dash-section ciop-dash-main-grid" aria-label="Lista e mapa">
        <div class="ciop-dash-panel card ciop-dash-main-list">
            <div class="card-header ciop-dash-card-head">
                <div>
                    <h2 class="card-title mb-0">Ocorrências recentes</h2>
                    <p class="ciop-dash-card-sub" id="dashboard-occurrences-updated">Atualização automática a cada 1 min</p>
                </div>
                <a href="{{ route('occurrences.index') }}" class="btn btn-sm btn-outline-secondary">Ver todas</a>
            </div>
            <div class="card-body">
                <div class="ciop-dash-occ-list" id="dashboard-occurrences-recent">
                    <div class="ciop-dash-occ-empty">Carregando…</div>
                </div>
            </div>
        </div>

        <div class="ciop-dash-panel card ciop-dash-main-map">
            <div class="card-header ciop-dash-card-head">
                <div>
                    <h2 class="card-title mb-0">Mapa das ocorrências</h2>
                    <p class="ciop-dash-card-sub">Geolocalização e equipes em deslocamento</p>
                </div>
                <div class="ciop-dash-map-tools">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="dashboard-heatmap-toggle">
                        <label class="custom-control-label" for="dashboard-heatmap-toggle">Mapa de calor</label>
                    </div>
                    <div class="ciop-dash-map-legend" aria-hidden="true">
                        <span><i class="fas fa-map-marker-alt" style="color:#c0392b"></i> Ocorrência</span>
                        <span><i class="fas fa-car" style="color:#1fa8a0"></i> Motorista</span>
                    </div>
                </div>
            </div>
            <div class="card-body p-0 position-relative">
                <div id="dashboard-map-loading" class="ciop-dash-map-loading">Carregando mapa…</div>
                <div id="dashboard-occurrences-map" class="ciop-dash-map" style="display: none;"></div>
            </div>
        </div>
    </section>

    <section class="ciop-dash-section" aria-labelledby="dash-charts-title">
        <div class="ciop-dash-section-head">
            <h2 id="dash-charts-title" class="ciop-dash-section-title">Análises</h2>
            <p class="ciop-dash-section-desc">Volume diário, distribuição por status e prioridade.</p>
        </div>

        <div class="ciop-dash-charts">
            <div class="ciop-dash-panel card ciop-dash-chart-wide">
                <div class="card-header">
                    <h3 class="card-title mb-0">Ocorrências por dia (últimos 14 dias)</h3>
                </div>
                <div class="card-body">
                    <div class="ciop-dash-chart-wrap">
                        <canvas id="chart-occurrences-by-day" height="120"></canvas>
                    </div>
                </div>
            </div>
            <div class="ciop-dash-panel card">
                <div class="card-header">
                    <h3 class="card-title mb-0">Por status</h3>
                </div>
                <div class="card-body">
                    <div class="ciop-dash-chart-wrap ciop-dash-chart-square">
                        <canvas id="chart-occurrences-by-status" height="160"></canvas>
                    </div>
                </div>
            </div>
            <div class="ciop-dash-panel card">
                <div class="card-header">
                    <h3 class="card-title mb-0">Por prioridade</h3>
                </div>
                <div class="card-body">
                    <div class="ciop-dash-chart-wrap ciop-dash-chart-square">
                        <canvas id="chart-occurrences-by-priority" height="160"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>

<script>window.dashboardChartsData = @json($charts);</script>
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

        function escapeHtml(value) {
            return String(value == null ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

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
                container.innerHTML = '<div class="ciop-dash-occ-empty">Nenhuma ocorrência recente.</div>';
                return;
            }
            container.innerHTML = items.map(function (o) {
                var title = escapeHtml(o.title || o.name || ('Ocorrência #' + o.id));
                var status = escapeHtml(o.status || '—');
                var type = escapeHtml(o.type || '');
                var priority = escapeHtml(o.priority || '');
                var when = escapeHtml(o.updated_at_human || '');
                var color = escapeHtml(o.priority_color || '#0b4f4a');
                return '' +
                    '<a class="ciop-dash-occ-card" href="/admin/occurrences/' + o.id + '">' +
                        '<span class="ciop-dash-occ-dot" style="background:' + color + '" aria-hidden="true"></span>' +
                        '<span class="ciop-dash-occ-main">' +
                            '<span class="ciop-dash-occ-title">' + title + '</span>' +
                            '<span class="ciop-dash-occ-meta">' +
                                '<span class="ciop-dash-occ-badge">' + status + '</span>' +
                                (type ? '<span>' + type + '</span>' : '') +
                                (priority ? '<span>' + priority + '</span>' : '') +
                            '</span>' +
                        '</span>' +
                        '<span class="ciop-dash-occ-when">' + when + '</span>' +
                    '</a>';
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
                    if (updatedEl) updatedEl.textContent = 'Atualizado agora · renovação a cada 1 min';
                    if (typeof window.updateDashboardMap === 'function') window.updateDashboardMap(items);
                })
                .catch(function () {
                    if (container) container.innerHTML = '<div class="ciop-dash-occ-empty is-error">Erro ao carregar.</div>';
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
                        radius: 8, color: '#fff', weight: 2, fillColor: '#1fa8a0', fillOpacity: 1
                    });
                    var content = '<div style="min-width: 180px;">' +
                        '<strong><i class="fas fa-car"></i> ' + (d.driver_name || 'Motorista') + '</strong>' +
                        '<p class="mb-1 small">Em deslocamento</p>' +
                        (d.occurrence_id ? '<a href="/admin/occurrences/' + d.occurrence_id + '" class="small">Ver ocorrência #' + d.occurrence_id + '</a>' : '') + '</div>';
                    marker.bindPopup(content);
                    driverLayer.addLayer(marker);
                });
            };

            window.addEventListener('resize', function () {
                setTimeout(function () { map.invalidateSize(); }, 150);
            });

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

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', window.initDashboardMap);
        } else {
            window.initDashboardMap();
        }
    })();
</script>
@stop
