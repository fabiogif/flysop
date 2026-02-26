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
                <span>Rota do motorista <span id="driver-route-live-badge" class="badge badge-success ml-2"
                        style="display: none;">Ao vivo</span></span>
                <small class="text-muted" id="driver-route-updated">Atualização a cada 10s</small>
            </div>
            <div class="card-body p-0">
                <div id="occurrence-driver-route-map" style="height: 400px;"></div>
            </div>
        </div>
    @endif

    @if ($occurrences->driver_id)
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Rota do motorista</span>
            </div>
            <div class="card-body p-0">
                <driver-tracker occurrence-id="{{ $occurrences->id }}"
                    occurrence-lat="{{ $occurrences->latitude ?: '-12.95307' }}"
                    occurrence-lng="{{ $occurrences->longitude ?: '-38.49706' }}" :height="400">
                </driver-tracker>
            </div>
        </div>
    @endif

@endsection