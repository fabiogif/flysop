@extends('adminlte::page')

@section('title', 'Alterar Plano')

@section('content_header')
    <h1 class="m-0 text-dark">Alterar Plano</h1>
@stop

@section('content')
    <div class="row clearfix">

        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('plans.update', $plan->id) }}" class="form" method="POST">
                        @csrf
                        @method('PUT')
                        @include('admin.pages.plans._partials.form')
                    </form>
                </div><!--card-body-->
            </div><!--card-->
        </div>

@endsection

