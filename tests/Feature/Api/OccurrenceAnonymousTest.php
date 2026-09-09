<?php

namespace Tests\Feature\Api;

use App\Models\Client;
use App\Models\Issuing;
use App\Models\StatusOccurrence;
use App\Models\Tenant;
use App\Models\TypeOccurrence;
use App\Services\ClientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OccurrenceAnonymousTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Buraco na via',
            'type_occurrences_id' => TypeOccurrence::factory()->create()->id,
            'issuings_id' => Issuing::factory()->create()->id,
            'lgpd_consent' => '1',
        ], $overrides);
    }

    public function test_anonymous_occurrence_uses_placeholder_client_without_email(): void
    {
        Tenant::factory()->create();
        StatusOccurrence::factory()->create(['is_terminal' => false, 'sort_order' => 0]);

        $response = $this->postJson('/api/occurrences', $this->validPayload([
            'is_anonymous' => true,
        ]));

        $response->assertCreated();

        $client = Client::where('phone', ClientService::ANONYMOUS_PHONE)->first();
        $this->assertNotNull($client);
        $this->assertDatabaseHas('occurrences', [
            'title' => 'Buraco na via',
            'clients_id' => $client->id,
        ]);
    }

    public function test_anonymous_placeholder_client_is_reused_across_submissions(): void
    {
        Tenant::factory()->create();
        StatusOccurrence::factory()->create(['is_terminal' => false, 'sort_order' => 0]);

        $this->postJson('/api/occurrences', $this->validPayload(['is_anonymous' => true]));
        $this->postJson('/api/occurrences', $this->validPayload(['is_anonymous' => true]));

        $this->assertSame(1, Client::where('phone', ClientService::ANONYMOUS_PHONE)->count());
    }

    public function test_occurrence_from_logged_citizen_is_attributed_to_that_client(): void
    {
        Tenant::factory()->create();
        StatusOccurrence::factory()->create(['is_terminal' => false, 'sort_order' => 0]);

        $auth = $this->postJson('/api/auth/citizen', ['name' => 'Maria', 'phone' => '11988887777']);
        $client = Client::where('phone', '11988887777')->firstOrFail();

        $this->postJson('/api/occurrences', $this->validPayload([
            'clients_id' => $client->id,
        ]));

        $this->assertDatabaseHas('occurrences', [
            'title' => 'Buraco na via',
            'clients_id' => $client->id,
        ]);
    }

    public function test_occurrence_without_email_is_accepted(): void
    {
        Tenant::factory()->create();
        StatusOccurrence::factory()->create(['is_terminal' => false, 'sort_order' => 0]);

        $this->postJson('/api/occurrences', $this->validPayload(['is_anonymous' => true]))
            ->assertCreated();
    }
}
