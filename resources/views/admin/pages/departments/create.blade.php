@extends('adminlte::page')

@section('title', 'Adicionar Departamento')

@section('content_header')
    <h1 class="m-0 text-dark">Adicionar Departamento</h1>
@stop

@section('content')
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('departments.store') }}" class="form" method="POST">
                        @csrf
                        @include('admin.pages.departments._partials.form')
                    </form>
                </div>
                <!--card-body-->
            </div>
            <!--card-->
        </div>
    </div>
@endsection
