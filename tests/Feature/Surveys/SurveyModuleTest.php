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

    public function test_admin_cannot_print_other_tenant_responses(): void
    {
        $this->actingAsAdmin();
        [$survey] = $this->surveyWithAnswer(Tenant::factory()->create()->id);

        $this->get(route('surveys.responses.pdf', $survey->id))
            ->assertRedirect(route('surveys.index'));
    }
}
