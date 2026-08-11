@extends('public.surveys.layout')

@section('title', 'Pesquisa encerrada')

@section('content')
    <article class="ciop-survey-card ciop-survey-card-center">
        <h1>{{ $survey->title }}</h1>
        <p class="ciop-survey-lead">Esta pesquisa está encerrada e não aceita novas respostas.</p>
    </article>
@endsection
