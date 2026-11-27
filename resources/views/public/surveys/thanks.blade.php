@extends('public.surveys.layout')

@section('title', 'Obrigado')

@section('content')
    <article class="ciop-survey-card ciop-survey-card-center">
        <h1>Obrigado!</h1>
        @if (!empty($alreadyAnswered))
            <p class="ciop-survey-lead">Você já respondeu “{{ $survey->title }}”. Cada pessoa pode responder uma vez.</p>
        @else
            <p class="ciop-survey-lead">Sua resposta para “{{ $survey->title }}” foi registrada.</p>
        @endif
    </article>
@endsection
