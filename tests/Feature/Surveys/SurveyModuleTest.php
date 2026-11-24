<?php

namespace Tests\Feature\Surveys;

use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class SurveyModuleTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        Config::set('acl.admin', ['admin-surveys@flysop.test']);
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'admin-surveys@flysop.test',
        ]);
        $this->actingAs($user);

        return $user;
    }

    public function test_admin_can_create_survey_with_questions_and_public_token(): void
    {
        $this->actingAsAdmin();

        $response = $this->post(route('surveys.store'), [
            'title' => 'Satisfação do atendimento',
            'description' => 'Conte como foi sua experiência',
            'is_active' => '1',
            'questions' => [
                [
                    'type' => 'text',
                    'prompt' => 'O que podemos melhorar?',
                    'required' => '1',
                    'options' => '',
                ],
                [
                    'type' => 'single_choice',
                    'prompt' => 'Você recomendaria o serviço?',
                    'required' => '1',
                    'options' => "Sim\nNão\nTalvez",
                ],
                [
                    'type' => 'scale',
                    'prompt' => 'Nota geral',
                    'required' => '1',
                    'options' => '',
                ],
            ],
        ]);

        $survey = Survey::first();
        $this->assertNotNull($survey);
        $response->assertRedirect(route('surveys.show', $survey->id));
        $this->assertNotEmpty($survey->public_token);
        $this->assertSame(3, $survey->questions()->count());
        $this->assertDatabaseHas('survey_questions', [
            'survey_id' => $survey->id,
            'type' => SurveyQuestion::TYPE_SINGLE_CHOICE,
            'prompt' => 'Você recomendaria o serviço?',
        ]);
    }

    public function test_public_can_view_and_submit_active_survey(): void
    {
        $tenant = Tenant::factory()->create();
        $survey = Survey::factory()->create([
            'tenant_id' => $tenant->id,
            'is_active' => true,
            'title' => 'Pesquisa pública',
        ]);
        $text = SurveyQuestion::factory()->create([
            'survey_id' => $survey->id,
            'type' => SurveyQuestion::TYPE_TEXT,
            'prompt' => 'Comentário',
            'sort_order' => 0,
        ]);
        $choice = SurveyQuestion::factory()->singleChoice(['Ótimo', 'Ruim'])->create([
            'survey_id' => $survey->id,
            'prompt' => 'Avaliação',
            'sort_order' => 1,
        ]);
        $scale = SurveyQuestion::factory()->scale()->create([
            'survey_id' => $survey->id,
            'prompt' => 'Nota',
            'sort_order' => 2,
        ]);

        $this->get(route('public.surveys.show', $survey->public_token))
            ->assertOk()
            ->assertSee('Pesquisa pública')
            ->assertSee('Comentário');

        $this->post(route('public.surveys.store', $survey->public_token), [
            'answers' => [
                $text->id => 'Muito bom atendimento',
                $choice->id => 'Ótimo',
                $scale->id => 5,
            ],
        ])->assertRedirect(route('public.surveys.thanks', $survey->public_token));

        $this->assertDatabaseHas('survey_responses', [
            'survey_id' => $survey->id,
        ]);
        $this->assertSame(1, SurveyResponse::where('survey_id', $survey->id)->count());
        $this->assertDatabaseHas('survey_answers', [
            'survey_question_id' => $text->id,
            'value' => 'Muito bom atendimento',
        ]);
        $this->assertDatabaseHas('survey_answers', [
            'survey_question_id' => $choice->id,
            'value' => 'Ótimo',
        ]);
        $this->assertDatabaseHas('survey_answers', [
            'survey_question_id' => $scale->id,
            'value' => '5',
        ]);
    }

    public function test_inactive_survey_does_not_accept_responses(): void
    {
        $survey = Survey::factory()->inactive()->create();
        $question = SurveyQuestion::factory()->create([
            'survey_id' => $survey->id,
            'prompt' => 'Pergunta',
        ]);

        $this->get(route('public.surveys.show', $survey->public_token))
            ->assertOk()
            ->assertSee('encerrada');

        $this->post(route('public.surveys.store', $survey->public_token), [
            'answers' => [
                $question->id => 'resposta',
            ],
        ])->assertRedirect(route('public.surveys.show', $survey->public_token));

        $this->assertSame(0, SurveyResponse::where('survey_id', $survey->id)->count());
    }

    public function test_admin_can_fetch_survey_qrcode(): void
    {
        $user = $this->actingAsAdmin();
        $survey = Survey::factory()->create(['tenant_id' => $user->tenant_id]);

        $response = $this->get(route('surveys.qrcode', $survey->id));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/png');
        $this->assertNotEmpty($response->getContent());
        $this->assertSame("\x89PNG\r\n\x1a\n", substr($response->getContent(), 0, 8));
    }

    public function test_admin_cannot_fetch_qrcode_for_other_tenant_survey(): void
    {
        $this->actingAsAdmin();
        $otherTenant = Tenant::factory()->create();
        $survey = Survey::factory()->create(['tenant_id' => $otherTenant->id]);

        $this->get(route('surveys.qrcode', $survey->id))
            ->assertRedirect(route('surveys.index'));
    }

    public function test_public_validation_rejects_invalid_choice_and_scale(): void
    {
        $survey = Survey::factory()->create(['is_active' => true]);
        $choice = SurveyQuestion::factory()->singleChoice(['A', 'B'])->create([
            'survey_id' => $survey->id,
            'prompt' => 'Escolha',
            'required' => true,
        ]);
        $scale = SurveyQuestion::factory()->scale()->create([
            'survey_id' => $survey->id,
            'prompt' => 'Nota',
            'required' => true,
        ]);

        $this->from(route('public.surveys.show', $survey->public_token))
            ->post(route('public.surveys.store', $survey->public_token), [
                'answers' => [
                    $choice->id => 'C',
                    $scale->id => 9,
                ],
            ])
            ->assertRedirect(route('public.surveys.show', $survey->public_token))
            ->assertSessionHasErrors([
                "answers.{$choice->id}",
                "answers.{$scale->id}",
            ]);

        $this->assertSame(0, SurveyResponse::where('survey_id', $survey->id)->count());
    }

    private function surveyWithAnswer(int $tenantId): array
    {
        $survey = Survey::factory()->create(['tenant_id' => $tenantId, 'is_active' => true]);
        $question = SurveyQuestion::factory()->singleChoice(['Sim', 'Não'])->create([
            'survey_id' => $survey->id,
            'prompt' => 'Recomendaria?',
        ]);
        $response = SurveyResponse::create(['survey_id' => $survey->id, 'submitted_at' => now()]);
        SurveyAnswer::create([
            'survey_response_id' => $response->id,
            'survey_question_id' => $question->id,
            'value' => 'Sim',
        ]);

        return [$survey, $question];
    }

    public function test_editing_survey_keeps_existing_answers(): void
    {
        $user = $this->actingAsAdmin();
        [$survey, $question] = $this->surveyWithAnswer($user->tenant_id);

        $this->put(route('surveys.update', $survey->id), [
            'title' => 'Título novo',
            'is_active' => '1',
            'questions' => [
                ['id' => $question->id, 'type' => 'single_choice', 'prompt' => 'Recomendaria o serviço?', 'required' => '1', 'options' => "Sim\nNão"],
                ['type' => 'text', 'prompt' => 'Comentário', 'required' => '0', 'options' => ''],
            ],
        ])->assertRedirect(route('surveys.show', $survey->id));

        $this->assertDatabaseHas('survey_questions', ['id' => $question->id, 'prompt' => 'Recomendaria o serviço?']);
        $this->assertSame(1, SurveyAnswer::where('survey_question_id', $question->id)->count());
        $this->assertSame(2, $survey->questions()->count());
    }

    public function test_editing_survey_ignores_question_id_from_other_survey(): void
    {
        $user = $this->actingAsAdmin();
        $survey = Survey::factory()->create(['tenant_id' => $user->tenant_id]);
        [, $foreign] = $this->surveyWithAnswer(Tenant::factory()->create()->id);

        $this->put(route('surveys.update', $survey->id), [
            'title' => 'Minha pesquisa',
            'questions' => [
                ['id' => $foreign->id, 'type' => 'text', 'prompt' => 'Sequestrada', 'required' => '1', 'options' => ''],
            ],
        ])->assertRedirect(route('surveys.show', $survey->id));

        $this->assertDatabaseHas('survey_questions', ['id' => $foreign->id, 'prompt' => 'Recomendaria?']);
        $this->assertDatabaseHas('survey_questions', ['survey_id' => $survey->id, 'prompt' => 'Sequestrada']);
    }

    public function test_responses_page_shows_summary_and_print_link(): void
    {
        $user = $this->actingAsAdmin();
        [$survey] = $this->surveyWithAnswer($user->tenant_id);

        $this->get(route('surveys.responses', $survey->id))
            ->assertOk()
            ->assertSee('Resumo por pergunta')
            ->assertSee('1 (100%)')
            ->assertSee(route('surveys.responses.pdf', $survey->id));
    }

    public function test_admin_can_print_responses_as_pdf(): void
    {
        $user = $this->actingAsAdmin();
        [$survey] = $this->surveyWithAnswer($user->tenant_id);

        $response = $this->get(route('surveys.responses.pdf', $survey->id));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertSame('%PDF', substr($response->getContent(), 0, 4));
    }

    public function test_admin_can_print_single_response_as_pdf(): void
    {
        $user = $this->actingAsAdmin();
        [$survey] = $this->surveyWithAnswer($user->tenant_id);
        $response = SurveyResponse::where('survey_id', $survey->id)->first();

        $this->get(route('surveys.responses', $survey->id))
            ->assertSee(route('surveys.response.pdf', [$survey->id, $response->id]));

        $pdf = $this->get(route('surveys.response.pdf', [$survey->id, $response->id]));
        $pdf->assertOk();
        $pdf->assertHeader('Content-Type', 'application/pdf');
        $this->assertSame('%PDF', substr($pdf->getContent(), 0, 4));
    }

    public function test_single_response_pdf_rejects_response_from_other_survey(): void
    {
        $user = $this->actingAsAdmin();
        [$mine] = $this->surveyWithAnswer($user->tenant_id);
        [$foreign] = $this->surveyWithAnswer(Tenant::factory()->create()->id);
        $foreignResponse = SurveyResponse::where('survey_id', $foreign->id)->first();

        $this->get(route('surveys.response.pdf', [$mine->id, $foreignResponse->id]))
            ->assertRedirect(route('surveys.responses', $mine->id));
    }

    public function test_period_filter_limits_responses_and_summary(): void
    {
        $user = $this->actingAsAdmin();
        [$survey, $question] = $this->surveyWithAnswer($user->tenant_id);
        $old = SurveyResponse::create(['survey_id' => $survey->id, 'submitted_at' => now()->subDays(10)]);
        SurveyAnswer::create(['survey_response_id' => $old->id, 'survey_question_id' => $question->id, 'value' => 'Não']);

        $period = ['date_from' => now()->subDay()->toDateString(), 'date_to' => now()->toDateString()];
        $summary = app(\App\Services\SurveyService::class)->summarize($survey, $period);
        $this->assertSame(1, $summary[0]['total']);
        $this->assertSame(['Sim' => 1, 'Não' => 0], $summary[0]['counts']);

        $this->get(route('surveys.responses', ['id' => $survey->id] + $period))
            ->assertOk()
            ->assertDontSee('Resposta #' . $old->id)
            ->assertSee(e(route('surveys.responses.pdf', ['id' => $survey->id] + $period)), false);

        $this->get(route('surveys.responses', ['id' => $survey->id, 'date_from' => '2026-05-10', 'date_to' => '2026-05-01']))
            ->assertSessionHasErrors('date_to');
    }

    public function test_admin_can_delete_single_response(): void
    {
        $user = $this->actingAsAdmin();
        [$survey] = $this->surveyWithAnswer($user->tenant_id);
        $response = SurveyResponse::where('survey_id', $survey->id)->first();

        $this->from(route('surveys.responses', $survey->id))
            ->delete(route('surveys.response.destroy', [$survey->id, $response->id]))
            ->assertRedirect(route('surveys.responses', $survey->id))
            ->assertSessionHas('messageSuccess');

        $this->assertDatabaseMissing('survey_responses', ['id' => $response->id]);
        $this->assertDatabaseMissing('survey_answers', ['survey_response_id' => $response->id]);

        $log = \Spatie\Activitylog\Models\Activity::where('description', 'survey.response_deleted')->first();
        $this->assertNotNull($log);
        $this->assertSame($user->id, (int) $log->causer_id);
        $this->assertSame($survey->id, (int) $log->subject_id);
        $this->assertSame($response->id, $log->properties['survey_response_id']);
        $this->assertSame(1, $log->properties['answers_count']);
        $this->assertSame($user->tenant_id, $log->properties['tenant_id']);
    }

    public function test_admin_cannot_delete_response_from_other_tenant(): void
    {
        $user = $this->actingAsAdmin();
        [$mine] = $this->surveyWithAnswer($user->tenant_id);
        [$foreign] = $this->surveyWithAnswer(Tenant::factory()->create()->id);
        $foreignResponse = SurveyResponse::where('survey_id', $foreign->id)->first();

        // Pela pesquisa alheia e pela própria pesquisa com id de resposta alheia.
        $this->delete(route('surveys.response.destroy', [$foreign->id, $foreignResponse->id]))
            ->assertRedirect(route('surveys.index'));
        $this->delete(route('surveys.response.destroy', [$mine->id, $foreignResponse->id]));

        $this->assertDatabaseHas('survey_responses', ['id' => $foreignResponse->id]);
        $this->assertDatabaseMissing('activity_log', ['description' => 'survey.response_deleted']);
    }

    public function test_admin_cannot_print_other_tenant_responses(): void
    {
        $this->actingAsAdmin();
        [$survey] = $this->surveyWithAnswer(Tenant::factory()->create()->id);

        $this->get(route('surveys.responses.pdf', $survey->id))
            ->assertRedirect(route('surveys.index'));
    }
}
