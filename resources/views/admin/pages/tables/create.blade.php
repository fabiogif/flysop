@extends('adminlte::page')

@section('title', 'Adicionar Mesa')

@section('content_header')
    <h1 class="m-0 text-dark">Adicionar Mesas</h1>
@stop

@section('content')
    <div class="row clearfix">

        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('tables.store') }}" class="form" method="POST">
                        @csrf
                        @include('admin.pages.tables._partials.form')
                    </form>
                </div>
                <!--card-body-->
            </div>
            <!--card-->
        </div>

    @endsection
