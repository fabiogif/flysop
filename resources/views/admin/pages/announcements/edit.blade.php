@extends('adminlte::page')

@section('title', 'Alterar Informativo')

@section('content_header')
    @include('admin.includes.page-header', [
        'title' => 'Alterar Informativo',
        'subtitle' => $announcement->title,
        'breadcrumbs' => [
            ['label' => 'Painel de Controle', 'url' => route('admin.index')],
            ['label' => 'Informativos', 'url' => route('announcements.index')],
            ['label' => 'Alterar'],
        ],
    ])
@stop

@section('content')
    <form action="{{ route('announcements.update', $announcement->id) }}" method="POST" class="form">
        @csrf
        @method('PUT')
        @include('admin.pages.announcements._partials.form')
    </form>
@endsection
