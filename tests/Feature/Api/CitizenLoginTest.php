<?php

namespace Tests\Feature\Api;

use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Login do cidadão no app (POST /api/sanctum/token): e-mail + senha do cadastro.
 */
class CitizenLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_citizen_logs_in_with_email_and_password_and_uses_the_token(): void
    {
        $client = Client::factory()->create(['email' => 'maria@example.com', 'password' => bcrypt('senha1234')]);

        $token = $this->postJson('/api/sanctum/token', [
            'email' => 'maria@example.com', 'password' => 'senha1234', 'device_name' => 'ciop-app',
        ])->assertOk()->assertJsonPath('client.id', $client->id)->json('token');

        $this->withToken($token)->getJson('/api/auth/me')->assertOk()->assertJsonPath('data.email', 'maria@example.com');
    }

    public function test_wrong_password_unknown_email_and_missing_fields_are_rejected(): void
    {
        Client::factory()->create(['email' => 'maria@example.com', 'password' => bcrypt('senha1234')]);

        $this->postJson('/api/sanctum/token', ['email' => 'maria@example.com', 'password' => 'errada', 'device_name' => 'ciop-app'])
            ->assertStatus(401)->assertJsonPath('message', 'E-mail ou senha inválidos.');
        $this->postJson('/api/sanctum/token', ['email' => 'ninguem@example.com', 'password' => 'senha1234', 'device_name' => 'ciop-app'])
            ->assertStatus(401);
        $this->postJson('/api/sanctum/token', ['email' => 'maria@example.com'])
            ->assertStatus(422)->assertJsonValidationErrors(['password', 'device_name']);
    }

    public function test_login_is_throttled_against_brute_force(): void
    {
        $payload = ['email' => 'maria@example.com', 'password' => 'errada', 'device_name' => 'ciop-app'];
        foreach (range(1, 5) as $attempt) {
            $this->postJson('/api/sanctum/token', $payload)->assertStatus(401);
        }

        $this->postJson('/api/sanctum/token', $payload)->assertStatus(429);
    }
}
