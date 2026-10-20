<?php

namespace Tests\Feature\Api;

use App\Models\Client;
use App\Models\Issuing;
use App\Models\Occurrences;
use App\Models\StatusOccurrence;
use App\Models\Tenant;
use App\Models\TypeOccurrence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyOccurrencesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_request_is_rejected(): void
    {
        $this->getJson('/api/my-occurrences')->assertUnauthorized();
    }

    public function test_citizen_can_only_list_own_occurrences(): void
    {
        $tenant = Tenant::factory()->create();
        $status = StatusOccurrence::factory()->create();
        $type = TypeOccurrence::factory()->create();
        $issuing = Issuing::factory()->create();

        $clientA = Client::factory()->create(['tenant_id' => $tenant->id]);
        $clientB = Client::factory()->create(['tenant_id' => $tenant->id]);

        // 2 ocorrências do cidadão A
        Occurrences::create([
            'protocol' => 'OC-2026-00001',
            'title' => 'Ocorrência A1',
            'clients_id' => $clientA->id,
            'status_occurrences_id' => $status->id,
            'type_occurrences_id' => $type->id,
            'issuings_id' => $issuing->id,
        ]);

        Occurrences::create([
            'protocol' => 'OC-2026-00002',
            'title' => 'Ocorrência A2',
            'clients_id' => $clientA->id,
            'status_occurrences_id' => $status->id,
            'type_occurrences_id' => $type->id,
            'issuings_id' => $issuing->id,
        ]);

        // 1 ocorrência do cidadão B
        Occurrences::create([
            'protocol' => 'OC-2026-00003',
            'title' => 'Ocorrência B1',
            'clients_id' => $clientB->id,
            'status_occurrences_id' => $status->id,
            'type_occurrences_id' => $type->id,
            'issuings_id' => $issuing->id,
        ]);

        // Consulta autenticada como Cidadão A
        $response = $this->actingAs($clientA, 'sanctum')
            ->getJson('/api/my-occurrences');

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
        // A ordem é "mais recente primeiro" (updated_at); o que importa aqui é o isolamento entre cidadãos
        $this->assertEqualsCanonicalizing(
            ['Ocorrência A1', 'Ocorrência A2'],
            collect($response->json('data'))->pluck('title')->all()
        );
    }
}
