<?php

namespace Tests\Feature\Api;

use App\Models\Client;
use App\Models\Issuing;
use App\Models\Occurrences;
use App\Models\StatusOccurrence;
use App\Models\User;
use App\Services\ClientService;
use Database\Seeders\StatusOccurrenceDriverSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Eixo 4 — Fase B: histórico para o autor, aviso de conclusão e reabertura pelo cidadão.
 */
class CitizenFollowUpTest extends TestCase
{
    use RefreshDatabase;

    private Issuing $issuing;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StatusOccurrenceDriverSeeder::class);
        $this->issuing = Issuing::factory()->create();
    }

    private function status(string $name): int
    {
        return StatusOccurrence::where('name', $name)->value('id');
    }

    private function occurrenceOf(Client $client, string $status = 'Recebida'): Occurrences
    {
        return Occurrences::factory()->create([
            'clients_id' => $client->id,
            'issuings_id' => $this->issuing->id,
            'status_occurrences_id' => $this->status($status),
        ]);
    }

    private function finalizeAsManager(Occurrences $occurrence): void
    {
        $manager = User::factory()->create(['issuing_id' => $this->issuing->id]);
        $this->actingAs($manager, 'sanctum')
            ->patchJson("/api/manager/occurrences/{$occurrence->id}/status", ['status_occurrences_id' => $this->status('Finalizada')])
            ->assertOk();
    }

    public function test_citizen_is_notified_when_occurrence_is_concluded(): void
    {
        $citizen = Client::factory()->create();
        $occurrence = $this->occurrenceOf($citizen);

        $this->finalizeAsManager($occurrence);

        $this->actingAs($citizen, 'sanctum')
            ->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.data.protocol', $occurrence->protocol)
            ->assertJsonPath('data.0.data.status', 'Finalizada');
    }

    public function test_shared_anonymous_client_is_not_notified(): void
    {
        \App\Models\Tenant::factory()->create();
        $anonymous = app(ClientService::class)->anonymousClient();
        $occurrence = $this->occurrenceOf($anonymous);

        $this->finalizeAsManager($occurrence);

        $this->assertSame(0, $anonymous->notifications()->count());
    }

    public function test_author_sees_history_without_staff_names(): void
    {
        $citizen = Client::factory()->create();
        $occurrence = $this->occurrenceOf($citizen);
        $this->finalizeAsManager($occurrence);

        $history = $this->actingAs($citizen, 'sanctum')
            ->getJson("/api/occurrences/{$occurrence->id}")
            ->assertOk()
            ->json('data.history');

        $this->assertSame('Finalizada', $history[0]['status']);
        $this->assertSame(['status', 'date'], array_keys($history[0]));
    }

    public function test_citizen_reopens_own_concluded_occurrence(): void
    {
        $citizen = Client::factory()->create();
        $occurrence = $this->occurrenceOf($citizen, 'Finalizada');

        $this->actingAs($citizen, 'sanctum')
            ->postJson("/api/occurrences/{$occurrence->id}/reopen", ['reason' => 'O buraco voltou'])
            ->assertOk()
            ->assertJsonPath('data.status_occurrences_id', $this->status('Reaberta'));

        $this->assertDatabaseHas('occurrence_status_history', [
            'occurrence_id' => $occurrence->id,
            'to_status_id' => $this->status('Reaberta'),
            'changed_by_user_id' => null,
            'note' => 'Reaberta pelo cidadão: O buraco voltou',
        ]);
    }

    public function test_citizen_cannot_reopen_others_open_or_expired_occurrences(): void
    {
        $citizen = Client::factory()->create();
        $this->actingAs($citizen, 'sanctum');

        $others = $this->occurrenceOf(Client::factory()->create(), 'Finalizada');
        $this->postJson("/api/occurrences/{$others->id}/reopen")->assertNotFound();

        $open = $this->occurrenceOf($citizen, 'Em atendimento');
        $this->postJson("/api/occurrences/{$open->id}/reopen")->assertStatus(422);

        $expired = $this->occurrenceOf($citizen, 'Finalizada');
        // Concluída há 31 dias; uma edição/avaliação recente (updated_at agora) não estende o prazo.
        \App\Models\OccurrenceStatusHistory::forceCreate([
            'occurrence_id' => $expired->id,
            'to_status_id' => $this->status('Finalizada'),
            'created_at' => now()->subDays(31),
        ]);
        $expired->update(['rating' => 5]);
        $this->postJson("/api/occurrences/{$expired->id}/reopen")->assertStatus(422);

        $this->assertSame($this->status('Finalizada'), (int) $others->fresh()->status_occurrences_id);
    }
}
