@extends('adminlte::page')

@section('title', 'Adicionar Pesquisa')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => 'Adicionar Pesquisa',
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Pesquisas', 'url' => route('surveys.index')],
            ['label' => 'Adicionar'],
        ],
    ])
@stop

@section('content')
    <form action="{{ route('surveys.store') }}" method="POST" class="form">
        @csrf
        @include('admin.pages.surveys._partials.form')
    </form>
@endsection
