<?php

namespace Tests\Feature\Api;

use App\Models\Client;
use App\Models\Issuing;
use App\Models\Occurrences;
use App\Models\StatusOccurrence;
use App\Models\Team;
use App\Models\User;
use App\Services\DashboardService;
use Database\Seeders\StatusOccurrenceDriverSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Eixo 4 — Fase C: encaminhamento para equipe da secretaria e avaliação do cidadão.
 */
class TeamForwardAndRatingTest extends TestCase
{
    use RefreshDatabase;

    private Issuing $obras;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StatusOccurrenceDriverSeeder::class);
        $this->obras = Issuing::factory()->create();
    }

    private function status(string $name): int
    {
        return StatusOccurrence::where('name', $name)->value('id');
    }

    public function test_manager_forwards_own_occurrence_to_own_or_shared_team_only(): void
    {
        $manager = User::factory()->create(['issuing_id' => $this->obras->id]);
        $occurrence = Occurrences::factory()->create(['issuings_id' => $this->obras->id]);
        $ownTeam = Team::create(['name' => 'Manutenção Obras', 'issuing_id' => $this->obras->id]);
        $shared = Team::create(['name' => 'Apoio geral']);
        $foreign = Team::create(['name' => 'Equipe Saúde', 'issuing_id' => Issuing::factory()->create()->id]);

        $this->actingAs($manager, 'sanctum');

        $this->getJson('/api/manager/teams')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->patchJson("/api/manager/occurrences/{$occurrence->id}/team", ['team_id' => $foreign->id])->assertStatus(422);
        $this->assertNull($occurrence->fresh()->team_id);

        $this->patchJson("/api/manager/occurrences/{$occurrence->id}/team", ['team_id' => $shared->id])->assertOk();
        $this->patchJson("/api/manager/occurrences/{$occurrence->id}/team", ['team_id' => $ownTeam->id])->assertOk();

        $this->assertSame($ownTeam->id, $occurrence->fresh()->team_id);
        $this->assertDatabaseHas('occurrence_status_history', [
            'occurrence_id' => $occurrence->id,
            'changed_by_user_id' => $manager->id,
            'note' => 'Encaminhada para a equipe Manutenção Obras.',
        ]);
    }

    public function test_admin_forwards_from_occurrence_page(): void
    {
        \Illuminate\Support\Facades\Config::set('acl.admin', ['admin-team@flysop.test']);
        $tenant = \App\Models\Tenant::factory()->create();
        $admin = User::factory()->create(['tenant_id' => $tenant->id, 'email' => 'admin-team@flysop.test']);
        $occurrence = Occurrences::factory()->create(['issuings_id' => $this->obras->id]);
        $team = Team::create(['name' => 'Tapa-buraco', 'issuing_id' => $this->obras->id]);

        $this->actingAs($admin);

        $this->get(route('occurrences.show', $occurrence->id))->assertOk()->assertSee('Equipe responsável')->assertSee('Tapa-buraco');

        $this->post(route('occurrences.forward-team', $occurrence->id), ['team_id' => $team->id])
            ->assertRedirect(route('occurrences.show', $occurrence->id));
        $this->assertSame($team->id, $occurrence->fresh()->team_id);
    }

    public function test_manager_cannot_forward_other_issuing_occurrence(): void
    {
        $manager = User::factory()->create(['issuing_id' => $this->obras->id]);
        $other = Occurrences::factory()->create();
        $shared = Team::create(['name' => 'Apoio geral']);

        $this->actingAs($manager, 'sanctum')
            ->patchJson("/api/manager/occurrences/{$other->id}/team", ['team_id' => $shared->id])
            ->assertForbidden();
    }

    public function test_citizen_rates_own_concluded_occurrence_once_and_feeds_satisfaction(): void
    {
        $citizen = Client::factory()->create();
        $occurrence = Occurrences::factory()->create([
            'clients_id' => $citizen->id,
            'status_occurrences_id' => $this->status('Finalizada'),
        ]);

        $this->actingAs($citizen, 'sanctum');

        $this->postJson("/api/occurrences/{$occurrence->id}/rating", ['rating' => 6])->assertStatus(422);

        $this->postJson("/api/occurrences/{$occurrence->id}/rating", ['rating' => 4, 'comment' => 'Rápido'])
            ->assertOk()
            ->assertJsonPath('data.rating', 4);

        $this->postJson("/api/occurrences/{$occurrence->id}/rating", ['rating' => 1])->assertStatus(422);
        $this->assertSame(4, $occurrence->fresh()->rating);

        $indicators = app(DashboardService::class)->getIndicators();
        $this->assertSame(4.0, $indicators['satisfacao']);
        $this->assertSame(1, $indicators['avaliacoes']);
    }

    public function test_citizen_cannot_rate_others_or_unfinished_occurrence(): void
    {
        $citizen = Client::factory()->create();
        $this->actingAs($citizen, 'sanctum');

        $others = Occurrences::factory()->create(['status_occurrences_id' => $this->status('Finalizada')]);
        $this->postJson("/api/occurrences/{$others->id}/rating", ['rating' => 5])->assertNotFound();

        $open = Occurrences::factory()->create([
            'clients_id' => $citizen->id,
            'status_occurrences_id' => $this->status('Em atendimento'),
        ]);
        $this->postJson("/api/occurrences/{$open->id}/rating", ['rating' => 5])->assertStatus(422);

        $this->assertNull($others->fresh()->rating);
    }
}
