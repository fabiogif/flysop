<?php

namespace Tests\Feature\Api;

use App\Models\Client;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitizenAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_phone_only_login_no_longer_exists(): void
    {
        // Entrava na conta só com nome + celular, sem senha: quem soubesse o celular de alguém
        // via as ocorrências dele. O app entra por e-mail + senha.
        Tenant::factory()->create();
        $victim = Client::factory()->create(['phone' => '11988887777']);

        $this->postJson('/api/auth/citizen', ['name' => 'Qualquer', 'phone' => $victim->phone])
            ->assertNotFound();
    }

    public function test_citizen_can_fetch_own_profile_with_token(): void
    {
        $client = Client::factory()->create(['name' => 'Maria Cidadã']);
        $token = $client->createToken('citizen')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('data.name', 'Maria Cidadã');
    }
}
