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
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ManagerAccessTest extends TestCase
{
    use RefreshDatabase;

    private function manager(Issuing $issuing, array $attributes = []): User
    {
        return User::factory()->create(array_merge(['issuing_id' => $issuing->id], $attributes));
    }

    public function test_manager_logs_in_and_me_identifies_the_role(): void
    {
        $issuing = Issuing::factory()->create(['name' => 'Secretaria de Obras']);
        $manager = $this->manager($issuing, ['email' => 'gestor@example.com']);

        $token = $this->postJson('/api/auth/staff/login', [
            'email' => 'gestor@example.com',
            'password' => 'password',
            'device_name' => 'app',
        ])->assertOk()
            ->assertJsonPath('user.role', 'manager')
            ->assertJsonPath('user.issuing.name', 'Secretaria de Obras')
            ->json('token');

        $this->withToken($token)->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('data.id', $manager->id)
            ->assertJsonPath('data.role', 'manager');
    }

    public function test_staff_login_rejects_wrong_password_and_users_without_issuing(): void
    {
        $this->manager(Issuing::factory()->create(), ['email' => 'gestor@example.com']);
        User::factory()->create(['email' => 'operador@example.com']);

        $this->postJson('/api/auth/staff/login', ['email' => 'gestor@example.com', 'password' => 'errada', 'device_name' => 'app'])
            ->assertStatus(401);

        $this->postJson('/api/auth/staff/login', ['email' => 'operador@example.com', 'password' => 'password', 'device_name' => 'app'])
            ->assertStatus(403);
    }

    public function test_citizen_occurrence_goes_to_the_type_issuing_and_notifies_its_managers(): void
    {
        Notification::fake();
        Tenant::factory()->create();
        StatusOccurrence::factory()->create(['is_terminal' => false, 'sort_order' => 0]);
        Issuing::factory()->create(); // primeiro órgão (fallback) — não deve ser usado
        $obras = Issuing::factory()->create();
        $type = TypeOccurrence::factory()->create(['issuing_id' => $obras->id]);
        $gestorObras = $this->manager($obras);
        $gestorPausado = $this->manager($obras, ['notify_new_occurrences' => false]);
        $gestorOutroOrgao = $this->manager(Issuing::factory()->create());

        $this->postJson('/api/occurrences', [
            'title' => 'Buraco na rua',
            'type_occurrences_id' => $type->id,
            'lgpd_consent' => '1',
        ])->assertCreated();

        $this->assertDatabaseHas('occurrences', ['title' => 'Buraco na rua', 'issuings_id' => $obras->id]);
        Notification::assertSentTo($gestorObras, NewOccurrenceForIssuingNotification::class);
        Notification::assertNotSentTo([$gestorPausado, $gestorOutroOrgao], NewOccurrenceForIssuingNotification::class);
    }

    public function test_manager_only_sees_and_changes_occurrences_of_own_issuing(): void
    {
        $obras = Issuing::factory()->create();
        $manager = $this->manager($obras);
        $own = Occurrences::factory()->create(['issuings_id' => $obras->id]);
        $other = Occurrences::factory()->create();
        $next = StatusOccurrence::factory()->create(['is_terminal' => false]);

        $this->actingAs($manager, 'sanctum');

        $this->getJson('/api/manager/occurrences')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $own->id);

        $this->getJson("/api/manager/occurrences/{$other->id}")->assertForbidden();
        $this->patchJson("/api/manager/occurrences/{$other->id}/status", ['status_occurrences_id' => $next->id])->assertForbidden();

        $this->getJson("/api/manager/occurrences/{$own->id}")->assertOk()->assertJsonStructure(['data', 'history']);
        $this->patchJson("/api/manager/occurrences/{$own->id}/status", ['status_occurrences_id' => $next->id])
            ->assertOk()
            ->assertJsonPath('data.status_occurrences_id', $next->id);

        $this->assertDatabaseHas('occurrence_status_history', [
            'occurrence_id' => $own->id,
            'to_status_id' => $next->id,
            'changed_by_user_id' => $manager->id,
        ]);
    }

    public function test_tokens_do_not_cross_between_citizen_and_manager_routes(): void
    {
        $this->actingAs(Client::factory()->create(), 'sanctum')
            ->getJson('/api/manager/occurrences')
            ->assertForbidden();

        $this->actingAs($this->manager(Issuing::factory()->create()), 'sanctum')
            ->getJson('/api/my-occurrences')
            ->assertForbidden();
    }

    public function test_notification_feed_marks_read_and_deletes(): void
    {
        $issuing = Issuing::factory()->create();
        $manager = $this->manager($issuing);
        $first = Occurrences::factory()->create(['issuings_id' => $issuing->id]);
        $second = Occurrences::factory()->create(['issuings_id' => $issuing->id]);
        $manager->notify(new NewOccurrenceForIssuingNotification($first));
        $this->travel(1)->minutes();
        $manager->notify(new NewOccurrenceForIssuingNotification($second));

        $this->actingAs($manager, 'sanctum');

        $feed = $this->getJson('/api/manager/notifications')
            ->assertOk()
            ->assertJsonPath('meta.unread_count', 2)
            ->assertJsonPath('data.0.data.occurrence_id', $second->id) // mais recente primeiro
            ->json('data');

        $this->deleteJson("/api/manager/notifications/{$feed[1]['id']}")->assertNoContent();
        $this->postJson('/api/manager/notifications/read-all')->assertNoContent();

        $this->getJson('/api/manager/notifications')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.unread_count', 0);

        $this->putJson('/api/manager/notification-settings', ['notify_new_occurrences' => false])->assertOk();
        $this->assertFalse($manager->fresh()->notify_new_occurrences);
    }

    public function test_citizen_map_and_other_peoples_occurrences_hide_personal_data(): void
    {
        $citizen = Client::factory()->create();
        $other = Occurrences::factory()->create(['cpf' => '111.222.333-44', 'email' => 'vizinho@example.com']);
        $own = Occurrences::factory()->create(['clients_id' => $citizen->id]);

        $this->actingAs($citizen, 'sanctum');

        $this->getJson('/api/occurrences')
            ->assertOk()
            ->assertJsonMissing(['cpf' => '111.222.333-44'])
            ->assertJsonMissing(['email' => 'vizinho@example.com']);

        $public = $this->getJson("/api/occurrences/{$other->id}")->assertOk()->json('data');
        $this->assertArrayNotHasKey('cpf', $public);
        $this->assertArrayNotHasKey('email', $public);

        $this->getJson("/api/occurrences/{$own->id}")
            ->assertOk()
            ->assertJsonPath('data.email', $own->email);

        $this->getJson("/api/occurrences/getOccurrenceByClientId/{$other->clients_id}")->assertForbidden();
    }
}
