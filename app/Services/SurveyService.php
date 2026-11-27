<?php

namespace App\Services;

use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class SurveyService
{
    public const ALREADY_ANSWERED = 'Você já respondeu esta pesquisa.';

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

    /**
     * Atualiza no lugar as perguntas que voltaram com id, cria as novas e apaga só as
     * removidas. Apagar todas e recriar (como era antes) disparava o cascade de
     * survey_answers e destruía todas as respostas já coletadas a cada edição.
     * O id só é aceito se pertencer a esta pesquisa — id de outra pesquisa/tenant vira
     * pergunta nova, nunca edita a alheia.
     */
    public function syncQuestions(Survey $survey, array $questions): void
    {
        $existing = $survey->questions()->get()->keyBy('id');
        $keptIds = [];

        foreach (array_values($questions) as $index => $question) {
            $type = $question['type'];
            $options = null;

            if ($type === SurveyQuestion::TYPE_SINGLE_CHOICE) {
                $options = array_values(array_filter(array_map('trim', $question['options'] ?? []), fn ($o) => $o !== ''));
            }

            $attributes = [
                'type' => $type,
                'prompt' => $question['prompt'],
                'options' => $options,
                'required' => (bool) ($question['required'] ?? true),
                // Justificativa só faz sentido em múltipla escolha (ex.: Sim/Não + por quê).
                'allow_justification' => $type === SurveyQuestion::TYPE_SINGLE_CHOICE && (bool) ($question['allow_justification'] ?? false),
                'sort_order' => (int) ($question['sort_order'] ?? $index),
            ];

            $current = $existing->get((int) ($question['id'] ?? 0));
            if ($current) {
                $current->update($attributes);
                $keptIds[] = $current->id;
            } else {
                $survey->questions()->create($attributes);
            }
        }

        $removedIds = $existing->keys()->diff($keptIds);
        if ($removedIds->isNotEmpty()) {
            SurveyQuestion::whereIn('id', $removedIds)->delete();
        }
    }

    /**
     * Respostas da pesquisa no período (date_from/date_to, datas inclusivas, ambos opcionais).
     * Ponto único usado pela listagem, pelo PDF e pelo resumo — o filtro não diverge entre eles.
     *
     * @param  array{date_from?: ?string, date_to?: ?string}  $period
     */
    public function responsesQuery(Survey $survey, array $period = []): HasMany
    {
        return $survey->responses()
            ->when($period['date_from'] ?? null, fn ($q, $from) => $q->where('submitted_at', '>=', Carbon::parse($from)->startOfDay()))
            ->when($period['date_to'] ?? null, fn ($q, $to) => $q->where('submitted_at', '<=', Carbon::parse($to)->endOfDay()));
    }

    /**
     * Resumo por pergunta: total de respostas; contagem por opção (múltipla escolha e
     * escala, incluindo opções com zero) e média da escala. Texto livre só tem total.
     *
     * @param  array{date_from?: ?string, date_to?: ?string}  $period
     * @return array<int, array{question: SurveyQuestion, total: int, counts: array<string, int>, average: ?float}>
     */
    public function summarize(Survey $survey, array $period = []): array
    {
        $survey->loadMissing('questions');
        $questions = $survey->questions;
        $responseIds = $this->responsesQuery($survey, $period)->reorder()->select('id');

        $totals = SurveyAnswer::query()
            ->whereIn('survey_response_id', $responseIds)
            ->whereIn('survey_question_id', $questions->pluck('id'))
            ->selectRaw('survey_question_id, count(*) as total')
            ->groupBy('survey_question_id')
            ->pluck('total', 'survey_question_id');

        $byValue = SurveyAnswer::query()
            ->whereIn('survey_response_id', $responseIds)
            ->whereIn('survey_question_id', $questions->whereIn('type', [SurveyQuestion::TYPE_SINGLE_CHOICE, SurveyQuestion::TYPE_SCALE])->pluck('id'))
            ->selectRaw('survey_question_id, value, count(*) as total')
            ->groupBy('survey_question_id', 'value')
            ->get()
            ->groupBy('survey_question_id');

        return $questions->map(function (SurveyQuestion $question) use ($totals, $byValue) {
            $counts = [];
            if ($question->type === SurveyQuestion::TYPE_SINGLE_CHOICE) {
                $counts = array_fill_keys($question->options ?? [], 0);
            } elseif ($question->type === SurveyQuestion::TYPE_SCALE) {
                $counts = array_fill_keys(['1', '2', '3', '4', '5'], 0);
            }

            foreach ($byValue->get($question->id, collect()) as $row) {
                $counts[(string) $row->value] = (int) $row->total;
            }

            $average = null;
            if ($question->type === SurveyQuestion::TYPE_SCALE && array_sum($counts) > 0) {
                $weighted = 0;
                foreach ($counts as $value => $count) {
                    $weighted += (int) $value * $count;
                }
                $average = round($weighted / array_sum($counts), 2);
            }

            return [
                'question' => $question,
                'total' => (int) ($totals[$question->id] ?? 0),
                'counts' => $counts,
                'average' => $average,
            ];
        })->values()->all();
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
     * Chave anônima do respondente (HMAC com a APP_KEY): barra a segunda resposta sem gravar
     * quem respondeu. $identity ex.: "client:12", "device:<id do app>", "web:<cookie>".
     */
    public function respondentKey(?string $identity): ?string
    {
        return $identity ? hash_hmac('sha256', $identity, (string) config('app.key')) : null;
    }

    public function hasResponded(Survey $survey, ?string $respondentKey): bool
    {
        return $respondentKey !== null
            && $survey->responses()->where('respondent_key', $respondentKey)->exists();
    }

    /**
     * Ids das pesquisas já respondidas por esta chave (marca "Respondida" na lista do app).
     *
     * @return array<int, int>
     */
    public function answeredSurveyIds(?string $respondentKey): array
    {
        if ($respondentKey === null) {
            return [];
        }

        return SurveyResponse::where('respondent_key', $respondentKey)->pluck('survey_id')->all();
    }

    /**
     * @param  array<int|string, mixed>  $answers  keyed by question id
     * @param  array{ip?: ?string, justifications?: array<int|string, mixed>, respondent_name?: ?string, respondent_key?: ?string}  $meta
     *         justifications: por id de pergunta (só as com allow_justification); respondent_name: opcional,
     *         sem nome a resposta continua anônima; respondent_key: ver respondentKey()
     */
    public function submitResponse(Survey $survey, array $answers, array $meta = []): SurveyResponse
    {
        $justifications = $meta['justifications'] ?? [];
        $respondentKey = $meta['respondent_key'] ?? null;

        if (!$survey->is_active) {
            throw ValidationException::withMessages([
                'survey' => 'Esta pesquisa está encerrada.',
            ]);
        }

        if ($this->hasResponded($survey, $respondentKey)) {
            throw ValidationException::withMessages([
                'survey' => self::ALREADY_ANSWERED,
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

        try {
            $response = SurveyResponse::create([
                'survey_id' => $survey->id,
                'respondent_name' => trim((string) ($meta['respondent_name'] ?? '')) ?: null,
                'respondent_key' => $respondentKey,
                'submitted_at' => now(),
                'ip' => $meta['ip'] ?? null,
            ]);
        } catch (QueryException $e) {
            // Dois envios simultâneos do mesmo respondente: o índice único barra o segundo
            if (($e->errorInfo[0] ?? null) === '23505') {
                throw ValidationException::withMessages(['survey' => self::ALREADY_ANSWERED]);
            }
            throw $e;
        }

        foreach ($normalizedByQuestionId as $questionId => $normalized) {
            $justification = null;
            if ($questions[$questionId]->allow_justification && is_string($justifications[$questionId] ?? null)) {
                $justification = mb_substr(trim($justifications[$questionId]), 0, 2000) ?: null;
            }

            SurveyAnswer::create([
                'survey_response_id' => $response->id,
                'survey_question_id' => $questionId,
                'value' => $normalized,
                'justification' => $justification,
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

        if ($question->type === SurveyQuestion::TYPE_NUMBER) {
            $number = str_replace(',', '.', (string) $value);
            if (!is_numeric($number)) {
                throw ValidationException::withMessages([
                    "answers.{$question->id}" => 'Informe apenas números.',
                ]);
            }

            return $number;
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
