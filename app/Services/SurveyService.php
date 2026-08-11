<?php

namespace App\Services;

use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class SurveyService
{
    /**
     * Sem DB::transaction() envolvendo múltiplos statements — em produção (Fly.io) a
     * conexão passa por um pooler local (PgCat/PgBouncer-style, 127.0.0.1:5432) em modo
     * transaction-pooling, que não sustenta corretamente uma transação client-side com
     * vários statements: o erro real de um statement é atribuído ao statement SEGUINTE
     * ("SQLSTATE[25P02]: current transaction is aborted"), mesmo quando cada statement
     * individualmente é válido (confirmado: a mesma sequência roda sem erro no Postgres
     * direto do docker-compose.dev.yml, sem pooler). Mesmo padrão já usado nas migrations
     * (ver $withinTransaction = false em database/migrations/2026_08_10_203210_*). Custo
     * aceito: uma falha real no meio da sincronização de perguntas pode deixar a pesquisa
     * sem todas as perguntas — recuperável reabrindo a edição, não há efeito colateral
     * externo (notificação, cobrança etc.) que exija atomicidade forte aqui.
     */
    public function createSurvey(array $data, User $user): Survey
    {
        $survey = Survey::create([
            'tenant_id' => $user->tenant_id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        $this->syncQuestions($survey, $data['questions'] ?? []);

        return $survey->load('questions');
    }

    public function updateSurvey(Survey $survey, array $data): Survey
    {
        $survey->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        $this->syncQuestions($survey, $data['questions'] ?? []);

        return $survey->fresh('questions');
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

        // Valida TODAS as respostas antes de gravar qualquer coisa — sem isso, a única
        // razão para envolver a gravação num DB::transaction() era desfazer o que já
        // tinha sido salvo caso uma pergunta posterior falhasse a validação. Validando
        // tudo antes, a gravação não precisa mais de rollback, e evita o mesmo problema
        // de pooler (transaction-pooling não sustenta transação client-side multi-
        // statement em produção) que afeta createSurvey()/updateSurvey() — ver comentário lá.
        $normalizedByQuestionId = [];
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

            $normalizedByQuestionId[$questionId] = $this->normalizeAnswer($question, $value);
        }

        $response = SurveyResponse::create([
            'survey_id' => $survey->id,
            'submitted_at' => now(),
            'ip' => $ip,
        ]);

        foreach ($normalizedByQuestionId as $questionId => $normalized) {
            SurveyAnswer::create([
                'survey_response_id' => $response->id,
                'survey_question_id' => $questionId,
                'value' => $normalized,
            ]);
        }

        return $response->load('answers');
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
