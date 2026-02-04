@extends('adminlte::page')
@section('title', 'Ocorrência #' . $occurrence->id)

@section('content_header')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('driver.dashboard') }}">Painel Motorista</a></li>
        <li class="breadcrumb-item"><a href="{{ route('driver.occurrences.index') }}">Ocorrências</a></li>
        <li class="breadcrumb-item active">#{{ $occurrence->id }}</li>
    </ol>
    <h1 class="m-0 text-dark">Ocorrência: {{ $occurrence->title ?: $occurrence->name ?: '#' . $occurrence->id }}</h1>
@stop

@section('content')
    @include('admin.includes.alerts')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="badge badge-{{ $occurrence->statusOccurence ? 'info' : 'secondary' }}">{{ $occurrence->statusOccurence?->name ?? '—' }}</span>
            @if ($occurrence->statusOccurence && $occurrence->statusOccurence->name === 'Aguardando aceitação' && $occurrence->driver_id)
                <div class="d-inline-flex gap-1">
                    <form action="{{ route('driver.occurrences.accept', $occurrence->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Aceitar esta ocorrência?');">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Aceitar</button>
                    </form>
                    <form action="{{ route('driver.occurrences.reject', $occurrence->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Recusar esta ocorrência?');">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-times"></i> Recusar</button>
                    </form>
                </div>
            @endif
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Título</dt>
                <dd class="col-sm-9">{{ $occurrence->title ?: '—' }}</dd>

                <dt class="col-sm-3">Nome</dt>
                <dd class="col-sm-9">{{ $occurrence->name ?: '—' }}</dd>

                <dt class="col-sm-3">E-mail</dt>
                <dd class="col-sm-9">{{ $occurrence->email ?: '—' }}</dd>

                <dt class="col-sm-3">Telefone</dt>
                <dd class="col-sm-9">{{ $occurrence->phone ?: '—' }}</dd>

                <dt class="col-sm-3">Endereço</dt>
                <dd class="col-sm-9">{{ $occurrence->address ?: '—' }}</dd>

                <dt class="col-sm-3">Tipo</dt>
                <dd class="col-sm-9">{{ $occurrence->typeOccurrence?->name ?? '—' }}</dd>

                <dt class="col-sm-3">Última atualização</dt>
                <dd class="col-sm-9">{{ $occurrence->updated_at ? $occurrence->updated_at->format('d/m/Y H:i') : '—' }}</dd>
            </dl>

            @if ($occurrence->description)
                <hr>
                <h6>Descrição</h6>
                <p>{{ $occurrence->description }}</p>
            @endif
        </div>
    </div>

    @if ($occurrence->statusOccurence && !in_array($occurrence->statusOccurence->name, ['Aguardando aceitação', 'Recusada'], true))
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Compartilhar minha rota</span>
                <div>
                    <button type="button" id="driver-share-route-btn" class="btn btn-info btn-sm"><i class="fas fa-map-marker-alt"></i> Iniciar compartilhamento</button>
                    <button type="button" id="driver-stop-route-btn" class="btn btn-secondary btn-sm" style="display:none;"><i class="fas fa-stop"></i> Parar</button>
                    <span id="driver-route-status" class="ml-2 small text-muted"></span>
                </div>
            </div>
            <div class="card-body small text-muted">
                Ao ativar, sua posição será enviada periodicamente para que o painel admin acompanhe sua rota em tempo real.
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-header">Atualizar status</div>
            <div class="card-body">
                <form action="{{ route('driver.occurrences.update-status', $occurrence->id) }}" method="POST" class="form-inline">
                    @csrf
                    @method('PUT')
                    <div class="form-group mr-2">
                        <label for="status_occurrences_id" class="sr-only">Status</label>
                        <select name="status_occurrences_id" id="status_occurrences_id" class="form-control" required>
                            @foreach ($statusOccurrences as $s)
                                <option value="{{ $s->id }}" {{ (int) $occurrence->status_occurrences_id === (int) $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Atualizar</button>
                </form>
            </div>
        </div>
    @endif

    <div class="mt-3">
        <a href="{{ route('driver.occurrences.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
    </div>

    @if ($occurrence->statusOccurence && !in_array($occurrence->statusOccurence->name, ['Aguardando aceitação', 'Recusada'], true))
    <script>
    (function() {
        var positionUrl = '{{ route("driver.position.store") }}';
        var occurrenceId = {{ (int) $occurrence->id }};
        var csrf = '{{ csrf_token() }}';
        var watchId = null;
        var intervalId = null;
        var INTERVAL_MS = 10000;

        function getEl(id) { return document.getElementById(id); }

        function sendPosition(lat, lng, accuracy) {
            var form = new FormData();
            form.append('_token', csrf);
            form.append('latitude', lat);
            form.append('longitude', lng);
            form.append('occurrence_id', occurrenceId);
            if (accuracy != null) form.append('accuracy', accuracy);

            fetch(positionUrl, {
                method: 'POST',
                body: form,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var statusEl = getEl('driver-route-status');
                if (statusEl) statusEl.textContent = 'Última atualização: agora';
            })
            .catch(function() {
                var statusEl = getEl('driver-route-status');
                if (statusEl) statusEl.textContent = 'Erro ao enviar posição.';
            });
        }

        function startSharing() {
            if (!navigator.geolocation) {
                alert('Seu navegador não suporta geolocalização.');
                return;
            }

            getEl('driver-share-route-btn').style.display = 'none';
            getEl('driver-stop-route-btn').style.display = 'inline-block';
            getEl('driver-route-status').textContent = 'Obtendo posição…';

            watchId = navigator.geolocation.watchPosition(
                function(pos) {
                    var lat = pos.coords.latitude;
                    var lng = pos.coords.longitude;
                    var acc = pos.coords.accuracy;
                    sendPosition(lat, lng, acc);
                },
                function(err) {
                    getEl('driver-route-status').textContent = 'Erro: ' + (err.message || 'permissão negada ou indisponível');
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 5000 }
            );

            intervalId = setInterval(function() {
                navigator.geolocation.getCurrentPosition(
                    function(pos) {
                        sendPosition(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy);
                    },
                    function() {}
                );
            }, INTERVAL_MS);
        }

        function stopSharing() {
            if (watchId != null) {
                navigator.geolocation.clearWatch(watchId);
                watchId = null;
            }
            if (intervalId != null) {
                clearInterval(intervalId);
                intervalId = null;
            }
            getEl('driver-share-route-btn').style.display = 'inline-block';
            getEl('driver-stop-route-btn').style.display = 'none';
            getEl('driver-route-status').textContent = '';
        }

        getEl('driver-share-route-btn').addEventListener('click', startSharing);
        getEl('driver-stop-route-btn').addEventListener('click', stopSharing);

        window.addEventListener('beforeunload', function() { stopSharing(); });
    })();
    </script>
    @endif
@stop
