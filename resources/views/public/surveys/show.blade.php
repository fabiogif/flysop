@extends('public.surveys.layout')

@section('title', $survey->title)

@section('content')
    <article class="ciop-survey-card">
        <h1>{{ $survey->title }}</h1>
        @if ($survey->description)
            <p class="ciop-survey-lead">{{ $survey->description }}</p>
        @endif

        @if ($errors->any())
            <div class="ciop-survey-alert" role="alert">
                Verifique as respostas destacadas abaixo.
            </div>
        @endif

        <form method="POST" action="{{ route('public.surveys.store', $survey->public_token) }}" class="ciop-survey-form">
            @csrf
            @foreach ($survey->questions as $question)
                <fieldset class="ciop-survey-field {{ $errors->has('answers.'.$question->id) ? 'is-invalid' : '' }}">
                    <legend>
                        {{ $question->prompt }}
                        @if ($question->required)<span class="req" aria-hidden="true">*</span>@endif
                    </legend>

                    @if ($question->type === 'text')
                        <textarea name="answers[{{ $question->id }}]" rows="3"
                            {{ $question->required ? 'required' : '' }}>{{ old('answers.'.$question->id) }}</textarea>
                    @elseif ($question->type === 'single_choice')
                        <div class="ciop-survey-choices">
                            @foreach ($question->options ?? [] as $option)
                                <label class="ciop-survey-choice">
                                    <input type="radio"
                                        name="answers[{{ $question->id }}]"
                                        value="{{ $option }}"
                                        {{ old('answers.'.$question->id) === $option ? 'checked' : '' }}
                                        {{ $question->required ? 'required' : '' }}>
                                    <span>{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    @elseif ($question->type === 'scale')
                        <div class="ciop-survey-scale" role="radiogroup" aria-label="Escala de 1 a 5">
                            @for ($i = 1; $i <= 5; $i++)
                                <label>
                                    <input type="radio"
                                        name="answers[{{ $question->id }}]"
                                        value="{{ $i }}"
                                        {{ (string) old('answers.'.$question->id) === (string) $i ? 'checked' : '' }}
                                        {{ $question->required ? 'required' : '' }}>
                                    <span>{{ $i }}</span>
                                </label>
                            @endfor
                        </div>
                        <p class="ciop-survey-hint">1 = baixo · 5 = alto</p>
                    @endif

                    @error('answers.'.$question->id)
                        <p class="ciop-survey-error">{{ $message }}</p>
                    @enderror
                </fieldset>
            @endforeach

            <button type="submit" class="ciop-survey-submit">Enviar respostas</button>
        </form>
    </article>
@endsection
