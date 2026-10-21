@extends('adminlte::page')

@section('title', 'Adicionar Informativo')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => 'Adicionar Informativo',
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Informativos', 'url' => route('announcements.index')],
            ['label' => 'Adicionar'],
        ],
    ])
@stop

@section('content')
    <form action="{{ route('announcements.store') }}" method="POST" class="form">
        @csrf
        @include('admin.pages.announcements._partials.form')
    </form>
@endsection
