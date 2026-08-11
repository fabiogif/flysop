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

@php
    $dashboardMapsKey = config('services.google.maps_key') ?: env('GOOGLE_MAPS_API_KEY', '');
@endphp
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
        const API_KEY = @json($dashboardMapsKey);
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

        function loadMapScript(callback) {
            if (window.google && window.google.maps) { window[callback](); return; }
            if (!API_KEY || API_KEY.length < 10) {
                var loadingEl = document.getElementById('dashboard-map-loading');
                if (loadingEl) loadingEl.innerHTML = '<p class="text-warning mb-0">Chave do Google Maps não configurada. Defina GOOGLE_MAPS_API_KEY no .env.</p>';
                return;
            }
            var s = document.createElement('script');
            s.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(API_KEY) + '&libraries=visualization&callback=' + encodeURIComponent(callback);
            s.async = true;
            s.defer = true;
            document.head.appendChild(s);
        }

        function loadClustererScript(callback) {
            if (window.markerClusterer) { callback(); return; }
            var s = document.createElement('script');
            s.src = 'https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js';
            s.async = true;
            s.onload = callback;
            s.onerror = callback;
            document.head.appendChild(s);
        }

        window.initDashboardMap = function () {
            var mapEl = document.getElementById('dashboard-occurrences-map');
            var loadingEl = document.getElementById('dashboard-map-loading');
            if (!mapEl) return;

            var defaultCenter = { lat: -12.95307, lng: -38.49706 };
            var map = new google.maps.Map(mapEl, {
                center: defaultCenter,
                zoom: 10,
                fullscreenControl: true,
                streetViewControl: false,
                mapTypeControl: true,
                zoomControl: true
            });

            var markers = [];
            var driverMarkers = [];
            var clusterer = null;
            var heatmapLayer = new google.maps.visualization.HeatmapLayer({ data: [], map: null, radius: 30 });

            // Tempo real (Fase 4): ao invés de só depender do polling de 60s, escuta o canal
            // de broadcast e refaz o fetch imediatamente quando alguma ocorrência muda.
            // Polling continua rodando como fallback (ver setInterval mais abaixo).
            if (window.Echo) {
                window.Echo.private('occurrences-dashboard').listen('OccurrenceUpdated', function () {
                    refreshAll();
                });
            }

            function clearMarkers() {
                if (clusterer) { clusterer.clearMarkers(); }
                markers.forEach(function (m) { m.setMap(null); });
                markers = [];
            }

            window.updateDashboardMap = function (occurrences) {
                clearMarkers();
                var bounds = null;
                (occurrences || []).forEach(function (o) {
                    var lat = o.latitude != null ? parseFloat(o.latitude) : NaN;
                    var lng = o.longitude != null ? parseFloat(o.longitude) : NaN;
                    if (isNaN(lat) || isNaN(lng)) return;
                    var pos = { lat: lat, lng: lng };
                    var marker = new google.maps.Marker({
                        position: pos,
                        title: o.title || o.name || 'Ocorrência #' + o.id,
                        icon: 'http://maps.google.com/mapfiles/ms/icons/' + (o.priority_color ? 'red' : 'red') + '-dot.png'
                    });
                    var content = '<div class="p-2" style="min-width: 180px;">' +
                        '<strong>' + (o.title || o.name || 'Ocorrência #' + o.id) + '</strong>' +
                        '<p class="mb-1 small">' + (o.status || '—') + (o.type ? ' · ' + o.type : '') + (o.priority ? ' · <span style="color:' + (o.priority_color || '#6c757d') + '">' + o.priority + '</span>' : '') + '</p>' +
                        (o.address ? '<p class="mb-1 small text-muted">' + o.address + '</p>' : '') +
                        '<a href="/admin/occurrences/' + o.id + '" class="small">Ver detalhes</a></div>';
                    var infowindow = new google.maps.InfoWindow({ content: content });
                    marker.addListener('click', function () { infowindow.open(map, marker); });
                    markers.push(marker);
                    if (!bounds) bounds = new google.maps.LatLngBounds();
                    bounds.extend(pos);
                });

                if (!heatmapMode) {
                    if (window.markerClusterer && window.markerClusterer.MarkerClusterer) {
                        clusterer = new window.markerClusterer.MarkerClusterer({ map: map, markers: markers });
                    } else {
                        markers.forEach(function (m) { m.setMap(map); });
                    }
                }

                if (bounds && markers.length > 0) {
                    map.fitBounds(bounds, markers.length === 1 ? 40 : 60);
                }
                mapEl.style.display = 'block';
                if (loadingEl) loadingEl.style.display = 'none';
            };

            window.updateDashboardHeatmap = function (points) {
                var weighted = (points || []).map(function (p) {
                    return new google.maps.LatLng(p.lat, p.lng);
                });
                heatmapLayer.setData(weighted);
            };

            var heatmapToggle = document.getElementById('dashboard-heatmap-toggle');
            if (heatmapToggle) {
                heatmapToggle.addEventListener('change', function () {
                    heatmapMode = heatmapToggle.checked;
                    if (heatmapMode) {
                        clearMarkers();
                        heatmapLayer.setMap(map);
                        fetchHeatmap();
                    } else {
                        heatmapLayer.setMap(null);
                        window.updateDashboardMap(lastOccurrences);
                    }
                });
            }

            window.updateDashboardDriverMarkers = function (drivers) {
                driverMarkers.forEach(function (m) { m.setMap(null); });
                driverMarkers = [];
                (drivers || []).forEach(function (d) {
                    var lat = d.latitude != null ? parseFloat(d.latitude) : NaN;
                    var lng = d.longitude != null ? parseFloat(d.longitude) : NaN;
                    if (isNaN(lat) || isNaN(lng)) return;
                    var pos = { lat: lat, lng: lng };
                    var marker = new google.maps.Marker({
                        map: map,
                        position: pos,
                        title: d.driver_name + ' – Ocorrência #' + (d.occurrence_id || '—'),
                        icon: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png'
                    });
                    var content = '<div class="p-2" style="min-width: 180px;">' +
                        '<strong><i class="fas fa-car text-primary"></i> ' + (d.driver_name || 'Motorista') + '</strong>' +
                        '<p class="mb-1 small">Em deslocamento</p>' +
                        (d.occurrence_id ? '<a href="/admin/occurrences/' + d.occurrence_id + '" class="small">Ver ocorrência #' + d.occurrence_id + '</a>' : '') + '</div>';
                    var infow = new google.maps.InfoWindow({ content: content });
                    marker.addListener('click', function () { infow.open(map, marker); });
                    driverMarkers.push(marker);
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
        loadMapScript(function () {
            loadClustererScript(function () {
                window.initDashboardMap();
            });
        });
    })();
</script>
@stop
