<?php

namespace Tests\Feature\Api;

use App\Models\Client;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurveyApiTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsCitizen(Tenant $tenant): Client
    {
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        $this->actingAs($client, 'sanctum');

        return $client;
    }

    public function test_citizen_can_list_active_surveys(): void
    {
        $tenant = Tenant::factory()->create();
        $this->actingAsCitizen($tenant);

        Survey::factory()->create(['tenant_id' => $tenant->id, 'is_active' => true, 'title' => 'Ativa']);
        Survey::factory()->create(['tenant_id' => $tenant->id, 'is_active' => false, 'title' => 'Inativa']);

        $response = $this->getJson('/api/surveys');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.title', 'Ativa');
    }

    public function test_citizen_can_view_and_answer_a_survey(): void
    {
        $tenant = Tenant::factory()->create();
        $this->actingAsCitizen($tenant);

        $survey = Survey::factory()->create(['tenant_id' => $tenant->id, 'is_active' => true]);
        $question = SurveyQuestion::factory()->create([
            'survey_id' => $survey->id,
            'type' => SurveyQuestion::TYPE_TEXT,
            'required' => true,
        ]);

        $this->getJson("/api/surveys/{$survey->public_token}")
            ->assertOk()
            ->assertJsonPath('data.questions.0.id', $question->id);

        $this->postJson("/api/surveys/{$survey->public_token}/responses", [
            'answers' => [$question->id => 'Muito bom'],
        ])->assertCreated();

        $this->assertSame(1, SurveyResponse::where('survey_id', $survey->id)->count());
    }

    public function test_survey_endpoints_require_authentication(): void
    {
        $this->getJson('/api/surveys')->assertUnauthorized();
    }
}
