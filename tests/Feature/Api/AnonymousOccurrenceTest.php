<?php

namespace Tests\Feature\Api;

use App\Models\Client;
use App\Models\Issuing;
use App\Models\Occurrences;
use App\Models\StatusOccurrence;
use App\Models\Tenant;
use App\Models\TypeOccurrence;
use App\Models\User;
use App\Notifications\NewOccurrenceForIssuingNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AnonymousOccurrenceTest extends TestCase
{
    use RefreshDatabase;

    private Issuing $issuing;
    private TypeOccurrence $type;

    protected function setUp(): void
    {
        parent::setUp();
        Tenant::factory()->create();
        StatusOccurrence::factory()->create(['is_terminal' => false, 'sort_order' => 0]);
        $this->issuing = Issuing::factory()->create();
        $this->type = TypeOccurrence::factory()->create(['issuing_id' => $this->issuing->id]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Poste apagado',
            'type_occurrences_id' => $this->type->id,
            'lgpd_consent' => '1',
        ], $overrides);
    }

    public function test_logged_citizen_anonymous_keeps_tracking_but_agency_does_not_see_identity(): void
    {
        Notification::fake();
        $citizen = Client::factory()->create(['name' => 'Maria Sigilo']);
        $manager = User::factory()->create(['issuing_id' => $this->issuing->id]);

        $this->actingAs($citizen, 'sanctum')
            ->postJson('/api/occurrences', $this->payload(['is_anonymous' => '1']))
            ->assertCreated();

        $occurrence = Occurrences::where('title', 'Poste apagado')->firstOrFail();
        $this->assertTrue($occurrence->is_anonymous);
        $this->assertSame($citizen->id, $occurrence->clients_id);

        // o autor continua acompanhando
        $this->getJson('/api/my-occurrences')->assertOk()->assertJsonPath('data.0.id', $occurrence->id);

        // o órgão não sabe quem abriu
        Notification::assertSentTo($manager, NewOccurrenceForIssuingNotification::class,
            fn ($notification) => $notification->toArray($manager)['author'] === null);

        $this->actingAs($manager, 'sanctum')
            ->getJson("/api/manager/occurrences/{$occurrence->id}")
            ->assertOk()
            ->assertJsonPath('requester', null)
            ->assertJsonPath('data.clients_id', null)
            ->assertJsonPath('data.is_anonymous', true);
    }

    public function test_visitor_without_account_registers_and_follows_by_protocol(): void
    {
        $this->getJson('/api/typeOccurrence')->assertOk();
        $this->getJson('/api/occurrences')->assertOk();

        $protocol = $this->postJson('/api/occurrences', $this->payload())
            ->assertCreated()
            ->json('data.protocol');

        $occurrence = Occurrences::where('protocol', $protocol)->firstOrFail();
        $this->assertTrue($occurrence->is_anonymous);

        $this->getJson("/api/occurrences/protocol/{$protocol}")
            ->assertOk()
            ->assertJsonPath('data.title', 'Poste apagado');

        $this->getJson('/api/occurrences/protocol/OC-0000-99999')->assertNotFound();
    }

    public function test_occurrence_with_photo_is_created_once_with_its_evidence(): void
    {
        Storage::fake();
        $citizen = Client::factory()->create();

        // Duas submissões seguidas: antes, a segunda falhava ("Is a directory") com a ocorrência já gravada
        foreach ([1, 2] as $i) {
            $this->actingAs($citizen, 'sanctum')
                ->post('/api/occurrences', $this->payload([
                    'title' => "Poste com foto {$i}",
                    'anexo' => [UploadedFile::fake()->image("foto{$i}.png")],
                ]), ['Accept' => 'application/json'])
                ->assertCreated()
                ->assertJsonCount(1, 'data.anexo');
        }

        $occurrence = Occurrences::where('title', 'Poste com foto 2')->firstOrFail();
        $this->assertCount(1, $occurrence->imagens);
        $this->assertNull($occurrence->imagens->first()->uploaded_by_user_id);
        Storage::assertExists($occurrence->imagens->first()->url);
        $this->assertSame(2, Occurrences::count());
    }

    public function test_environment_without_status_or_issuing_returns_clear_message_instead_of_500(): void
    {
        StatusOccurrence::query()->delete();

        $this->postJson('/api/occurrences', $this->payload())
            ->assertStatus(503)
            ->assertJsonPath('message', 'O sistema ainda não tem status de ocorrência ou órgão cadastrado. Avise a administração da prefeitura.');

        $this->assertDatabaseCount('occurrences', 0);
    }

    public function test_public_creation_ignores_author_and_workflow_fields_from_payload(): void
    {
        $victim = Client::factory()->create();
        $finalizada = StatusOccurrence::factory()->create(['is_terminal' => true]);

        $this->postJson('/api/occurrences', $this->payload([
            'clients_id' => $victim->id,
            'status_occurrences_id' => $finalizada->id,
        ]))->assertCreated();

        $occurrence = Occurrences::where('title', 'Poste apagado')->firstOrFail();
        $this->assertNotSame($victim->id, $occurrence->clients_id);
        $this->assertNotSame($finalizada->id, $occurrence->status_occurrences_id);
    }
}
