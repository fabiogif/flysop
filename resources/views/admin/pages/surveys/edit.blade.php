@extends('adminlte::page')

@section('title', 'Alterar Pesquisa')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => 'Alterar Pesquisa',
        'subtitle' => $survey->title,
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Pesquisas', 'url' => route('surveys.index')],
            ['label' => 'Alterar'],
        ],
    ])
@stop

@section('content')
    <form action="{{ route('surveys.update', $survey->id) }}" method="POST" class="form">
        @csrf
        @method('PUT')
        @include('admin.pages.surveys._partials.form')
    </form>
@endsection
