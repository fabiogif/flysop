<?php

namespace Tests\Feature\Api;

use App\Models\Client;
use App\Models\Issuing;
use App\Models\StatusOccurrence;
use App\Models\Tenant;
use App\Models\TypeOccurrence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OccurrenceValidationTest extends TestCase
{
    use RefreshDatabase;

    private function basePayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Buraco na rua principal',
            'type_occurrences_id' => TypeOccurrence::factory()->create()->id,
            'lgpd_consent' => '1',
            'latitude' => -12.971400,
            'longitude' => -38.501400,
            'address' => 'Avenida Sete de Setembro',
            'neighborhood' => 'Centro',
            'city' => 'Salvador',
            'state' => 'BA',
            'zip' => '40060-001',
            'description' => 'Buraco profundo na pista da direita.',
        ], $overrides);
    }

    public function test_valid_coordinates_and_address_are_accepted(): void
    {
        Tenant::factory()->create();
        StatusOccurrence::factory()->create(['is_terminal' => false, 'sort_order' => 0]);
        Issuing::factory()->create();

        $response = $this->postJson('/api/occurrences', $this->basePayload());

        $response->assertCreated();
        $this->assertDatabaseHas('occurrences', [
            'title' => 'Buraco na rua principal',
            'latitude' => -12.971400,
            'longitude' => -38.501400,
            'neighborhood' => 'Centro',
        ]);
    }

    public function test_invalid_coordinates_fail_validation(): void
    {
        Tenant::factory()->create();

        $this->postJson('/api/occurrences', $this->basePayload(['latitude' => 95.0]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['latitude']);

        $this->postJson('/api/occurrences', $this->basePayload(['longitude' => -190.0]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['longitude']);
    }

    public function test_omitted_issuing_id_uses_default_issuing(): void
    {
        Tenant::factory()->create();
        StatusOccurrence::factory()->create(['is_terminal' => false, 'sort_order' => 0]);
        $defaultIssuing = Issuing::factory()->create();

        $response = $this->postJson('/api/occurrences', $this->basePayload());

        $response->assertCreated();
        $this->assertDatabaseHas('occurrences', [
            'issuings_id' => $defaultIssuing->id,
        ]);
    }

    public function test_authenticated_citizen_is_automatically_assigned(): void
    {
        $tenant = Tenant::factory()->create();
        StatusOccurrence::factory()->create(['is_terminal' => false, 'sort_order' => 0]);
        Issuing::factory()->create();
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($client, 'sanctum')
            ->postJson('/api/occurrences', $this->basePayload())
            ->assertCreated();

        $this->assertDatabaseHas('occurrences', [
            'title' => 'Buraco na rua principal',
            'clients_id' => $client->id,
        ]);
    }
}
