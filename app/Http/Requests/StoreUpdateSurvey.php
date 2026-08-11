<?php

namespace App\Http\Requests;

use App\Models\SurveyQuestion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUpdateSurvey extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $questions = $this->input('questions', []);
        if (!is_array($questions)) {
            return;
        }

        foreach ($questions as $i => $question) {
            if (!isset($question['options']) || !is_string($question['options'])) {
                continue;
            }
            $lines = preg_split('/\r\n|\r|\n/', $question['options']) ?: [];
            $questions[$i]['options'] = array_values(array_filter(array_map('trim', $lines), fn ($l) => $l !== ''));
        }

        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'questions' => $questions,
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:2', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.type' => ['required', Rule::in(SurveyQuestion::TYPES)],
            'questions.*.prompt' => ['required', 'string', 'min:2', 'max:500'],
            'questions.*.required' => ['sometimes', 'boolean'],
            'questions.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'questions.*.options' => ['nullable', 'array'],
            'questions.*.options.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($this->input('questions', []) as $index => $question) {
                if (($question['type'] ?? null) !== SurveyQuestion::TYPE_SINGLE_CHOICE) {
                    continue;
                }
                $options = $question['options'] ?? [];
                if (!is_array($options) || count($options) < 2) {
                    $validator->errors()->add(
                        "questions.{$index}.options",
                        'Perguntas de múltipla escolha precisam de pelo menos 2 opções.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'title.required' => 'O título da pesquisa é obrigatório.',
            'questions.required' => 'Inclua ao menos uma pergunta.',
            'questions.min' => 'Inclua ao menos uma pergunta.',
            'questions.*.prompt.required' => 'Informe o enunciado da pergunta.',
            'questions.*.type.in' => 'Tipo de pergunta inválido.',
        ];
    }
}
