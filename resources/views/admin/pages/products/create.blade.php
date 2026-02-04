@extends('adminlte::page')

@section('title', 'Adicionar Produto')

@section('content_header')
    <h1 class="m-0 text-dark">Adicionar Produto</h1>
@stop

@section('content')
    <div class="row clearfix">

        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('products.store') }}" class="form" method="POST" enctype="multipart/form-data">
                        @csrf
                        @include('admin.pages.products._partials.form')
                    </form>
                </div>
                <!--card-body-->
            </div>
            <!--card-->
        </div>

    @endsection
