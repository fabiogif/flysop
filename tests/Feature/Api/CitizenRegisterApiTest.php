<?php

namespace Tests\Feature\Api;

use App\Models\Client;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitizenRegisterApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_citizen_can_register_with_email_and_strong_password(): void
    {
        Tenant::factory()->create();

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Carlos Silva',
            'email' => 'carlos@example.com',
            'password' => 'SenhaSegura123',
            'phone' => '71988889999',
            'terms_accepted' => '1',
            'marketing_opt_in' => true,
        ]);

        $response->assertCreated();
        $response->assertJsonStructure(['token', 'client' => ['id', 'name', 'email']]);

        $this->assertDatabaseHas('clients', [
            'email' => 'carlos@example.com',
            'name' => 'Carlos Silva',
            'marketing_opt_in' => true,
        ]);
    }

    public function test_weak_password_fails_validation(): void
    {
        Tenant::factory()->create();

        // Senha curta (< 8)
        $this->postJson('/api/auth/register', [
            'name' => 'Carlos Silva',
            'email' => 'carlos@example.com',
            'password' => '12345',
            'terms_accepted' => '1',
        ])->assertStatus(422)->assertJsonValidationErrors(['password']);

        // Senha sem números
        $this->postJson('/api/auth/register', [
            'name' => 'Carlos Silva',
            'email' => 'carlos@example.com',
            'password' => 'somenteletras',
            'terms_accepted' => '1',
        ])->assertStatus(422)->assertJsonValidationErrors(['password']);
    }

    public function test_terms_accepted_is_required(): void
    {
        Tenant::factory()->create();

        $this->postJson('/api/auth/register', [
            'name' => 'Carlos Silva',
            'email' => 'carlos@example.com',
            'password' => 'SenhaSegura123',
        ])->assertStatus(422)->assertJsonValidationErrors(['terms_accepted']);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        $tenant = Tenant::factory()->create();
        Client::factory()->create(['tenant_id' => $tenant->id, 'email' => 'carlos@example.com']);

        $this->postJson('/api/auth/register', [
            'name' => 'Carlos Silva',
            'email' => 'carlos@example.com',
            'password' => 'SenhaSegura123',
            'terms_accepted' => '1',
        ])->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_social_auth_registers_and_authenticates_citizen(): void
    {
        Tenant::factory()->create();

        $response = $this->postJson('/api/auth/social', [
            'provider' => 'google',
            'provider_id' => 'google_sub_123456',
            'email' => 'googleuser@example.com',
            'name' => 'Google User',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['token', 'client']);

        $this->assertDatabaseHas('clients', [
            'google_id' => 'google_sub_123456',
            'email' => 'googleuser@example.com',
        ]);
    }
}
