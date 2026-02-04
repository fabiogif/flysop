@extends('adminlte::page')

@section('title', 'Alterar Perfil')

@section('content_header')
    <h1 class="m-0 text-dark">Alterar Perfil</h1>
@stop

@section('content')
    <div class="row clearfix">

        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('profiles.update', $profile->id) }}" class="form" method="POST">
                        @csrf
                        @method('PUT')
                        @include('admin.pages.profiles._partials.form')
                    </form>
                </div>
                <!--card-body-->
            </div>
            <!--card-->
        </div>

    @endsection
