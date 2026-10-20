<?php

namespace Tests\Feature\Api;

use App\Models\Client;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_profile_does_not_expose_private_fields(): void
    {
        $tenant = Tenant::factory()->create();
        $client = Client::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Maria Cidadã',
            'username' => 'mariacidada',
            'email' => 'secreto@example.com',
            'phone' => '71999998888',
            'bio' => 'Líder comunitária no bairro Centro.',
            'website' => 'https://mariacidada.org',
        ]);

        $response = $this->getJson('/api/profile/mariacidada');

        $response->assertOk();
        $response->assertJsonPath('data.username', 'mariacidada');
        $response->assertJsonPath('data.bio', 'Líder comunitária no bairro Centro.');
        $response->assertJsonPath('data.website', 'https://mariacidada.org');

        // Garante que campos confidenciais NUNCA são expostos
        $response->assertJsonMissing(['email' => 'secreto@example.com']);
        $response->assertJsonMissing(['phone' => '71999998888']);
        $this->assertArrayNotHasKey('email', $response->json('data'));
        $this->assertArrayNotHasKey('phone', $response->json('data'));
    }

    public function test_user_can_update_name_and_email(): void
    {
        $tenant = Tenant::factory()->create();
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($client, 'sanctum')
            ->putJson('/api/profile', [
                'name' => 'Novo Nome',
                'email' => 'novo@example.com',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Novo Nome')
            ->assertJsonPath('data.email', 'novo@example.com');

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'name' => 'Novo Nome',
            'email' => 'novo@example.com',
        ]);
    }

    public function test_profile_update_ignores_fields_not_editable_on_web(): void
    {
        $tenant = Tenant::factory()->create();
        $client = Client::factory()->create(['tenant_id' => $tenant->id, 'bio' => null]);

        $this->actingAs($client, 'sanctum')
            ->putJson('/api/profile', [
                'name' => 'Nome Válido',
                'email' => 'valido@example.com',
                'bio' => 'Não deve ser gravado.',
            ])
            ->assertOk();

        $this->assertDatabaseHas('clients', ['id' => $client->id, 'bio' => null]);
    }

    public function test_email_must_be_unique_per_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        Client::factory()->create(['tenant_id' => $tenant->id, 'email' => 'joao@example.com']);
        $client2 = Client::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($client2, 'sanctum')
            ->putJson('/api/profile', [
                'name' => 'Outro Nome',
                'email' => 'joao@example.com',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_avatar_upload_validates_and_stores_image(): void
    {
        Storage::fake('public');

        $tenant = Tenant::factory()->create();
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);

        $file = UploadedFile::fake()->image('avatar.jpg', 300, 300);

        $response = $this->actingAs($client, 'sanctum')
            ->postJson('/api/profile/avatar', [
                'avatar' => $file,
            ]);

        $response->assertOk();
        $response->assertJsonStructure(['avatar_url', 'client']);
        $this->assertNotNull($client->fresh()->avatar_url);
    }
}
