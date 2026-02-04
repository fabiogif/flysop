@extends('adminlte::page')

@section('title', "Detalhes Tipo de Ocorrência { $occurrences->title }")

@section('content_header')
    <h1 class="m-0 text-dark">Detalhes da Ocorrência <b>{{ $occurrences->title }}</b></h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @csrf
            <div class="row">
                <ul>
                    <li><b>Nome:</b> {{ $occurrences->title }}</li>
                    <li><b>Ultima Atualização:</b> {{ date('d/M/Y h:m:s', strtotime($occurrences->updated_at)) }}</li>
                    <li><b>E-mail:</b> {{ $occurrences->email }}</li>
                    <li><b>Telefone:</b> {{ $occurrences->phone }}</li>
                    <li><b>Endereço:</b> {{ $occurrences->address }}</li>
                    @foreach ($occurrencesImagens as $occurrencesImagen)
                        <li
                            style="padding:5px; background:#343a40!important;  border-radius:24px; list-style:none; margin:10px 0">
                            <img style=" border-radius:24px "
                                src="https://sopanexos.s3.amazonaws.com/{{ $occurrencesImagen->url }}"
                                alt="{{ $occurrences->title }}" width="500" height="500" />
                        </li>
                    @endforeach
                </ul>
            </div>
            <!--row-->
            @include('admin.includes.alerts')

            <form action="{{ route('occurrences.destroy', $occurrences->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="far fa-trash-alt"></i>
                    <span class=m-4>Excluir</span>
                </button>
            </form>
        </div>
        <!--card-body-->
    </div>
    <!--card-->

    @if ($occurrences->driver_id)
    <div class="card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Rota do motorista <span id="driver-route-live-badge" class="badge badge-success ml-2" style="display: none;">Ao vivo</span></span>
            <small class="text-muted" id="driver-route-updated">Atualização a cada 10s</small>
        </div>
        <div class="card-body p-0">
            <div id="occurrence-driver-route-map" style="height: 400px;"></div>
        </div>
    </div>
    @endif

    @if ($occurrences->driver_id)
    @php
        $occLat = $occurrences->latitude ? (float) $occurrences->latitude : -12.95307;
        $occLng = $occurrences->longitude ? (float) $occurrences->longitude : -38.49706;
        $driverRouteMapsKey = config('services.google.maps_key') ?: env('GOOGLE_MAPS_API_KEY', '');
        $broadcastDriver = config('broadcasting.default');
        $pusherKey = config('broadcasting.connections.pusher.key');
        $pusherCluster = config('broadcasting.connections.pusher.options.cluster', 'us2');
        $echoEnabled = ($broadcastDriver === 'pusher' && $pusherKey);
    @endphp
    <script>
(function() {
    var MAP_ID = 'occurrence-driver-route-map';
    var API_KEY = @json($driverRouteMapsKey);
    var OCC_LAT = {{ $occLat }};
    var OCC_LNG = {{ $occLng }};
    var OCCURRENCE_ID = {{ (int) $occurrences->id }};
    var DRIVER_ROUTE_URL = '{{ route("occurrences.driver-route", $occurrences->id) }}';
    var POLL_INTERVAL = 10000;
    var map = null;
    var occurrenceMarker = null;
    var driverMarker = null;
    var routePolyline = null;
    var currentRoutePath = [];

    function getEl(id) { return document.getElementById(id); }

    function loadMapScript(cb) {
        if (window.google && window.google.maps) { cb(); return; }
        if (!API_KEY || API_KEY.length < 10) return;
        var s = document.createElement('script');
        s.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(API_KEY) + '&callback=' + encodeURIComponent(cb);
        s.async = true;
        s.defer = true;
        document.head.appendChild(s);
    }

    function applyPositionUpdate(data) {
        if (!map) return;
        var pos = { lat: parseFloat(data.latitude), lng: parseFloat(data.longitude) };
        currentRoutePath.push(pos);
        if (routePolyline) {
            routePolyline.setPath(currentRoutePath);
        } else {
            routePolyline = new google.maps.Polyline({
                path: currentRoutePath,
                geodesic: true,
                strokeColor: '#2196F3',
                strokeOpacity: 0.8,
                strokeWeight: 4,
                map: map
            });
        }
        if (driverMarker) {
            driverMarker.setPosition(pos);
        } else {
            driverMarker = new google.maps.Marker({
                map: map,
                position: pos,
                title: (data.driver && data.driver.name) ? data.driver.name : 'Motorista',
                icon: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png'
            });
        }
        driverMarker.setVisible(true);
        map.panTo(pos);
        var liveBadge = getEl('driver-route-live-badge');
        if (liveBadge) liveBadge.style.display = 'inline';
        var updatedEl = getEl('driver-route-updated');
        if (updatedEl) updatedEl.textContent = 'Ao vivo';
    }

    function initMap() {
        var mapEl = getEl(MAP_ID);
        if (!mapEl) return;

        map = new google.maps.Map(mapEl, {
            center: { lat: OCC_LAT, lng: OCC_LNG },
            zoom: 14,
            fullscreenControl: true,
            zoomControl: true
        });

        occurrenceMarker = new google.maps.Marker({
            map: map,
            position: { lat: OCC_LAT, lng: OCC_LNG },
            title: 'Ocorrência',
            icon: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png'
        });

        window.applyDriverPositionUpdate = applyPositionUpdate;

        fetchRoute();
        setInterval(fetchRoute, POLL_INTERVAL);
    }

    function fetchRoute() {
        fetch(DRIVER_ROUTE_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.route && data.route.length > 0) {
                    var path = data.route.map(function(p) { return { lat: p.lat, lng: p.lng }; });
                    currentRoutePath = path;
                    if (routePolyline) routePolyline.setMap(null);
                    routePolyline = new google.maps.Polyline({
                        path: path,
                        geodesic: true,
                        strokeColor: '#2196F3',
                        strokeOpacity: 0.8,
                        strokeWeight: 4,
                        map: map
                    });
                }
                if (data.last_position) {
                    var pos = { lat: data.last_position.lat, lng: data.last_position.lng };
                    if (driverMarker) {
                        driverMarker.setPosition(pos);
                    } else {
                        driverMarker = new google.maps.Marker({
                            map: map,
                            position: pos,
                            title: data.driver ? data.driver.name : 'Motorista',
                            icon: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png'
                        });
                    }
                    driverMarker.setVisible(true);
                    map.panTo(pos);
                    var liveBadge = getEl('driver-route-live-badge');
                    var isLive = data.last_position.created_at && (Date.now() - new Date(data.last_position.created_at).getTime() < 60000);
                    if (liveBadge) liveBadge.style.display = isLive ? 'inline' : 'none';
                } else {
                    if (driverMarker) driverMarker.setVisible(false);
                    var liveBadge = getEl('driver-route-live-badge');
                    if (liveBadge) liveBadge.style.display = 'none';
                }
                var updatedEl = getEl('driver-route-updated');
                if (updatedEl) updatedEl.textContent = 'Atualizado agora';
            })
            .catch(function() {});
    }

    loadMapScript('initMap');
    window.initMap = initMap;
})();
</script>
    @if ($echoEnabled)
    <script>
(function() {
    var ECHO_KEY = @json($pusherKey);
    var ECHO_CLUSTER = @json($pusherCluster);
    var ECHO_CSRF = @json(csrf_token());
    var ECHO_AUTH = '{{ url("/broadcasting/auth") }}';
    var OCCURRENCE_ID = {{ (int) $occurrences->id }};

    function loadScript(src) {
        return new Promise(function(resolve, reject) {
            var s = document.createElement('script');
            s.src = src;
            s.onload = resolve;
            s.onerror = reject;
            document.head.appendChild(s);
        });
    }

    function initEcho() {
        if (window.driverRouteEchoSubscribed) return;
        loadScript('https://js.pusher.com/7.0/pusher.min.js')
            .then(function() {
                return loadScript('https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/2.2.4/echo.iife.min.js');
            })
            .then(function() {
                var EchoConstructor = window.Echo;
                if (typeof EchoConstructor !== 'function') return;
                window.driverRouteEcho = new EchoConstructor({
                    broadcaster: 'pusher',
                    key: ECHO_KEY,
                    cluster: ECHO_CLUSTER,
                    forceTLS: true,
                    authEndpoint: ECHO_AUTH,
                    auth: {
                        headers: {
                            'X-CSRF-TOKEN': ECHO_CSRF,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }
                });
                subscribeChannel();
            })
            .catch(function() {});
    }

    function subscribeChannel() {
        if (!window.driverRouteEcho) return;
        window.driverRouteEcho.private('occurrence.' + OCCURRENCE_ID)
            .listen('.DriverPositionUpdated', function(e) {
                if (typeof window.applyDriverPositionUpdate === 'function') {
                    window.applyDriverPositionUpdate(e);
                }
            });
        window.driverRouteEchoSubscribed = true;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEcho);
    } else {
        initEcho();
    }
})();
</script>
    @endif
    @endif
@endsection
