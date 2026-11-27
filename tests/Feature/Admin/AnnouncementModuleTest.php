<?php

namespace Tests\Feature\Admin;

use App\Models\Announcement;
use App\Models\Issuing;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class AnnouncementModuleTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private Issuing $issuing;
    private Issuing $otherIssuing;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        $this->issuing = Issuing::factory()->create();
        $this->otherIssuing = Issuing::factory()->create();
    }

    /**
     * Gestor com um perfil de acesso ao painel (admin.panel exige alguma permissão), mas sem a
     * permissão "announcements": o menu Informativos é liberado por ser gestor de órgão.
     */
    private function manager(): User
    {
        $manager = User::factory()->create(['tenant_id' => $this->tenant->id, 'issuing_id' => $this->issuing->id]);
        $role = Role::create(['name' => 'Gestor de órgão', 'description' => 'Teste']);
        $role->permissions()->attach(Permission::create(['name' => 'occurrences', 'description' => 'Ocorrências']));
        $manager->roles()->attach($role);

        return $manager;
    }

    private function payload(array $overrides = []): array
    {
        return array_merge(['title' => 'Interdição na Av. Central', 'body' => 'Obra até sexta.', 'is_published' => '1'], $overrides);
    }

    public function test_manager_publishes_only_for_own_agency_even_if_payload_says_otherwise(): void
    {
        $this->actingAs($this->manager())
            ->get(route('announcements.create'))
            ->assertOk()
            ->assertDontSee('name="issuing_id"', false); // gestor não escolhe órgão

        $this->post(route('announcements.store'), $this->payload(['issuing_id' => $this->otherIssuing->id]))
            ->assertRedirect(route('announcements.index'));

        $announcement = Announcement::firstOrFail();
        $this->assertSame($this->issuing->id, $announcement->issuing_id);
        $this->assertSame($this->tenant->id, $announcement->tenant_id);
        $this->assertNotNull($announcement->published_at);
    }

    public function test_manager_sees_and_edits_only_own_agency_announcements(): void
    {
        $own = Announcement::factory()->create(['tenant_id' => $this->tenant->id, 'issuing_id' => $this->issuing->id, 'title' => 'Do meu órgão']);
        $other = Announcement::factory()->create(['tenant_id' => $this->tenant->id, 'issuing_id' => $this->otherIssuing->id, 'title' => 'De outro órgão']);

        $this->actingAs($this->manager());

        $this->get(route('announcements.index'))->assertOk()->assertSee('Do meu órgão')->assertDontSee('De outro órgão');
        $this->get(route('announcements.edit', $own->id))->assertOk();
        $this->get(route('announcements.edit', $other->id))->assertNotFound();
        $this->put(route('announcements.update', $other->id), $this->payload())->assertNotFound();
        $this->delete(route('announcements.destroy', $other->id))->assertNotFound();
        $this->assertDatabaseHas('announcements', ['id' => $other->id]);
    }

    public function test_admin_must_choose_agency_and_validation_messages_are_in_portuguese(): void
    {
        Config::set('acl.admin', ['admin-informativos@flysop.test']);
        $admin = User::factory()->create(['tenant_id' => $this->tenant->id, 'email' => 'admin-informativos@flysop.test']);

        $this->actingAs($admin)
            ->get(route('announcements.create'))
            ->assertOk()
            ->assertSee('name="issuing_id"', false)
            ->assertSee($this->otherIssuing->name);

        $this->post(route('announcements.store'), $this->payload(['title' => '']))
            ->assertSessionHasErrors(['issuing_id' => 'Selecione o órgão do informativo.', 'title' => 'Informe o título.']);

        $this->post(route('announcements.store'), $this->payload(['issuing_id' => $this->otherIssuing->id, 'is_published' => '0']))
            ->assertRedirect(route('announcements.index'));

        $this->assertDatabaseHas('announcements', ['issuing_id' => $this->otherIssuing->id, 'is_published' => false, 'published_at' => null]);
    }

    public function test_user_without_permission_and_not_manager_is_forbidden(): void
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->actingAs($user)->get(route('announcements.index'))->assertForbidden();
    }

    public function test_site_home_shows_only_published_announcements(): void
    {
        Announcement::factory()->create(['tenant_id' => $this->tenant->id, 'issuing_id' => $this->issuing->id, 'title' => 'Aviso publicado']);
        Announcement::factory()->draft()->create(['tenant_id' => $this->tenant->id, 'issuing_id' => $this->issuing->id, 'title' => 'Aviso rascunho']);

        $this->get(route('site.home'))->assertOk()->assertSee('Aviso publicado')->assertDontSee('Aviso rascunho');
    }
}
