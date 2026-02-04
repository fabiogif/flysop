@extends('adminlte::page')

@section('title', 'Adicionar Ocorrência')

@section('content_header')
    <h1 class="m-0 text-dark">Adicionar Ocorrência</h1>
@stop
<style>
    #map {
        height: 100%;
    }
</style>
@section('content')
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-6">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('occurrences.store') }}" class="form" method="POST" enctype="multipart/form-data">
                        @csrf
                        @include('admin.pages.occurrences._partials.form')
                    </form>
                </div>
                <!--card-body-->
            </div>
            <!--card-->
        </div>
    </div>
@endsection
