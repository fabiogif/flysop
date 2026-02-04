@extends('adminlte::page')

@section('title', 'Adicionar Perfil')

@section('content_header')
    <h1 class="m-0 text-dark">Adicionar Perfil</h1>
@stop

@section('content')
    <div class="row clearfix">

        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('profiles.store') }}" class="form" method="POST">
                        @csrf
                        @include('admin.pages.profiles._partials.form')
                    </form>
                </div>
                <!--card-body-->
            </div>
            <!--card-->
        </div>

    @endsection
