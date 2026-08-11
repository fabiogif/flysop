@extends('public.surveys.layout')

@section('title', 'Obrigado')

@section('content')
    <article class="ciop-survey-card ciop-survey-card-center">
        <h1>Obrigado!</h1>
        <p class="ciop-survey-lead">Sua resposta para “{{ $survey->title }}” foi registrada.</p>
    </article>
@endsection
