<?php

namespace Tests\Feature\Api;

use App\Models\Client;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitizenAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_citizen_can_register_with_only_name_and_phone(): void
    {
        Tenant::factory()->create();

        $response = $this->postJson('/api/auth/citizen', [
            'name' => 'Maria Cidadã',
            'phone' => '11988887777',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['token', 'client' => ['id', 'name', 'email', 'phone']]);
        $this->assertDatabaseHas('clients', [
            'name' => 'Maria Cidadã',
            'phone' => '11988887777',
            'email' => null,
        ]);
    }

    public function test_citizen_registration_requires_name_and_phone(): void
    {
        Tenant::factory()->create();

        $this->postJson('/api/auth/citizen', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'phone']);
    }

    public function test_same_phone_reuses_the_same_client(): void
    {
        Tenant::factory()->create();

        $this->postJson('/api/auth/citizen', ['name' => 'Maria', 'phone' => '11988887777']);
        $this->postJson('/api/auth/citizen', ['name' => 'Maria Cidadã', 'phone' => '11988887777']);

        $this->assertSame(1, Client::where('phone', '11988887777')->count());
    }

    public function test_citizen_can_fetch_own_profile_with_token(): void
    {
        Tenant::factory()->create();

        $auth = $this->postJson('/api/auth/citizen', [
            'name' => 'Maria Cidadã',
            'phone' => '11988887777',
        ]);

        $token = $auth->json('token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('data.name', 'Maria Cidadã');
    }
}
