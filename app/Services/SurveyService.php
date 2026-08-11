<?php

namespace App\Services;

use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SurveyService
{
    public function createSurvey(array $data, User $user): Survey
    {
        return DB::transaction(function () use ($data, $user) {
            $survey = Survey::create([
                'tenant_id' => $user->tenant_id,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            $this->syncQuestions($survey, $data['questions'] ?? []);

            return $survey->load('questions');
        });
    }

    public function updateSurvey(Survey $survey, array $data): Survey
    {
        return DB::transaction(function () use ($survey, $data) {
            $survey->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'is_active' => (bool) ($data['is_active'] ?? false),
            ]);

            $this->syncQuestions($survey, $data['questions'] ?? []);

            return $survey->fresh('questions');
        });
    }

    public function syncQuestions(Survey $survey, array $questions): void
    {
        $survey->questions()->delete();

        foreach (array_values($questions) as $index => $question) {
            $type = $question['type'];
            $options = null;

            if ($type === SurveyQuestion::TYPE_SINGLE_CHOICE) {
                $options = array_values(array_filter(array_map('trim', $question['options'] ?? []), fn ($o) => $o !== ''));
            }

            $survey->questions()->create([
                'type' => $type,
                'prompt' => $question['prompt'],
                'options' => $options,
                'required' => (bool) ($question['required'] ?? true),
                'sort_order' => (int) ($question['sort_order'] ?? $index),
            ]);
        }
    }

    public function findActiveByToken(string $token): ?Survey
    {
        return Survey::with('questions')
            ->where('public_token', $token)
            ->where('is_active', true)
            ->first();
    }

    public function findByToken(string $token): ?Survey
    {
        return Survey::with('questions')
            ->where('public_token', $token)
            ->first();
    }

    /**
     * @param  array<int|string, mixed>  $answers  keyed by question id
     */
    public function submitResponse(Survey $survey, array $answers, ?string $ip = null): SurveyResponse
    {
        if (!$survey->is_active) {
            throw ValidationException::withMessages([
                'survey' => 'Esta pesquisa está encerrada.',
            ]);
        }

        $survey->loadMissing('questions');
        $questions = $survey->questions->keyBy('id');

        return DB::transaction(function () use ($survey, $answers, $ip, $questions) {
            $response = SurveyResponse::create([
                'survey_id' => $survey->id,
                'submitted_at' => now(),
                'ip' => $ip,
            ]);

            foreach ($questions as $questionId => $question) {
                $raw = $answers[$questionId] ?? null;
                $value = is_string($raw) ? trim($raw) : $raw;

                if ($question->required && ($value === null || $value === '')) {
                    throw ValidationException::withMessages([
                        "answers.{$questionId}" => 'Esta pergunta é obrigatória.',
                    ]);
                }

                if ($value === null || $value === '') {
                    continue;
                }

                $normalized = $this->normalizeAnswer($question, $value);

                SurveyAnswer::create([
                    'survey_response_id' => $response->id,
                    'survey_question_id' => $question->id,
                    'value' => $normalized,
                ]);
            }

            return $response->load('answers');
        });
    }

    /**
     * @param  mixed  $value
     */
    private function normalizeAnswer(SurveyQuestion $question, $value): string
    {
        if ($question->type === SurveyQuestion::TYPE_SCALE) {
            if (!is_numeric($value) || (int) $value < 1 || (int) $value > 5) {
                throw ValidationException::withMessages([
                    "answers.{$question->id}" => 'Informe um valor de 1 a 5.',
                ]);
            }

            return (string) (int) $value;
        }

        if ($question->type === SurveyQuestion::TYPE_SINGLE_CHOICE) {
            $options = $question->options ?? [];
            if (!in_array((string) $value, $options, true)) {
                throw ValidationException::withMessages([
                    "answers.{$question->id}" => 'Selecione uma das opções disponíveis.',
                ]);
            }

            return (string) $value;
        }

        return (string) $value;
    }

    public function toggleActive(Survey $survey): Survey
    {
        $survey->update(['is_active' => !$survey->is_active]);

        return $survey->fresh();
    }
}
