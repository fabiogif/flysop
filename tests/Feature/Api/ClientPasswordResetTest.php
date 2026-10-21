<?php

namespace Tests\Feature\Api;

use App\Models\Client;
use App\Notifications\ClientResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Recuperação de senha do cidadão: pedido pelo app (API) e troca na página web.
 */
class ClientPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_citizen_resets_password_through_the_emailed_link(): void
    {
        Notification::fake();
        $client = Client::factory()->create(['email' => 'maria@example.com', 'password' => bcrypt('antiga123')]);
        $client->createToken('ciop-app');

        $this->postJson('/api/auth/forgot-password', ['email' => 'maria@example.com'])->assertOk();

        $token = null;
        Notification::assertSentTo($client, ClientResetPasswordNotification::class, function ($n) use (&$token) {
            $token = $n->token;
            return true;
        });

        $this->get("/cidadao/redefinir-senha/{$token}?email=maria@example.com")->assertOk()->assertSee('nova senha');

        $this->post('/cidadao/redefinir-senha', [
            'token' => $token, 'email' => 'maria@example.com',
            'password' => 'nova12345', 'password_confirmation' => 'nova12345',
        ])->assertOk()->assertSee('Senha alterada');

        $client->refresh();
        $this->assertTrue(Hash::check('nova12345', $client->password));
        $this->assertSame(0, $client->tokens()->count());
    }

    public function test_unknown_email_gets_the_same_answer_and_bad_token_is_rejected(): void
    {
        Notification::fake();
        Client::factory()->create(['email' => 'maria@example.com', 'password' => bcrypt('antiga123')]);

        $this->postJson('/api/auth/forgot-password', ['email' => 'ninguem@example.com'])
            ->assertOk()->assertJsonPath('message', 'Se o e-mail estiver cadastrado, você receberá um link para criar uma nova senha.');
        Notification::assertNothingSent();

        $this->from('/cidadao/redefinir-senha/falso')->post('/cidadao/redefinir-senha', [
            'token' => 'falso', 'email' => 'maria@example.com',
            'password' => 'nova12345', 'password_confirmation' => 'nova12345',
        ])->assertRedirect('/cidadao/redefinir-senha/falso')->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('antiga123', Client::first()->password));
    }
}
