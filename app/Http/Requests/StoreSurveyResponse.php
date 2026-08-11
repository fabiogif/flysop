<?php

namespace App\Http\Requests;

use App\Models\Survey;
use App\Models\SurveyQuestion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSurveyResponse extends FormRequest
{
    protected ?Survey $surveyModel = null;

    public function authorize(): bool
    {
        return true;
    }

    public function survey(): ?Survey
    {
        if ($this->surveyModel) {
            return $this->surveyModel;
        }

        $token = $this->route('token');
        $this->surveyModel = Survey::with('questions')
            ->where('public_token', $token)
            ->first();

        return $this->surveyModel;
    }

    public function rules(): array
    {
        $survey = $this->survey();
        if (!$survey) {
            return [];
        }

        $rules = ['answers' => ['nullable', 'array']];

        foreach ($survey->questions as $question) {
            $key = "answers.{$question->id}";
            $rule = [$question->required ? 'required' : 'nullable'];

            if ($question->type === SurveyQuestion::TYPE_SCALE) {
                $rule = array_merge($rule, ['integer', 'min:1', 'max:5']);
            } elseif ($question->type === SurveyQuestion::TYPE_SINGLE_CHOICE) {
                $rule[] = 'string';
                $options = $question->options ?? [];
                if ($options) {
                    $rule[] = Rule::in($options);
                }
            } else {
                $rule = array_merge($rule, ['string', 'max:5000']);
            }

            $rules[$key] = $rule;
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'answers.*.required' => 'Esta pergunta é obrigatória.',
            'answers.*.in' => 'Selecione uma das opções disponíveis.',
            'answers.*.min' => 'Informe um valor de 1 a 5.',
            'answers.*.max' => 'Informe um valor válido.',
            'answers.*.integer' => 'Informe um valor de 1 a 5.',
        ];
    }
}
