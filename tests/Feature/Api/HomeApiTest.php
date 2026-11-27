<?php

namespace Tests\Feature\Api;

use App\Models\Announcement;
use App\Models\Issuing;
use App\Models\Occurrences;
use App\Models\StatusOccurrence;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Página inicial do app: totais por etapa e informativos — públicos (app sem conta).
 */
class HomeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_stats_group_statuses_filter_by_agency_and_need_no_login(): void
    {
        $defesaCivil = Issuing::factory()->create(['name' => 'Defesa Civil']);
        $obras = Issuing::factory()->create(['name' => 'Obras']);
        $status = fn (string $name, bool $terminal = false) => StatusOccurrence::factory()->create(['name' => $name, 'is_terminal' => $terminal]);
        [$recebida, $atendimento, $finalizada, $cancelada] = [$status('Recebida'), $status('Em atendimento'), $status('Finalizada', true), $status('Cancelada', true)];

        $create = fn (StatusOccurrence $s, Issuing $i, int $n = 1) => Occurrences::factory()->count($n)->create(['status_occurrences_id' => $s->id, 'issuings_id' => $i->id]);
        $create($recebida, $defesaCivil, 2);
        $create($atendimento, $defesaCivil);
        $create($finalizada, $obras);
        $create($cancelada, $defesaCivil); // não conta em nenhum grupo

        $this->getJson('/api/occurrence-stats')
            ->assertOk()
            ->assertExactJson([
                'data' => ['abertas' => 2, 'andamento' => 1, 'resolvidas' => 1],
                'issuings' => [['id' => $defesaCivil->id, 'name' => 'Defesa Civil'], ['id' => $obras->id, 'name' => 'Obras']],
            ]);

        $this->getJson("/api/occurrence-stats?issuing_id={$obras->id}")
            ->assertJsonPath('data', ['abertas' => 0, 'andamento' => 0, 'resolvidas' => 1]);
    }

    public function test_announcements_list_only_published_and_filter_by_agency(): void
    {
        $tenant = Tenant::factory()->create();
        $a = Issuing::factory()->create();
        $b = Issuing::factory()->create();
        Announcement::factory()->create(['tenant_id' => $tenant->id, 'issuing_id' => $a->id, 'title' => 'Aviso A', 'published_at' => now()->subDay()]);
        Announcement::factory()->create(['tenant_id' => $tenant->id, 'issuing_id' => $b->id, 'title' => 'Aviso B', 'published_at' => now()]);
        Announcement::factory()->draft()->create(['tenant_id' => $tenant->id, 'issuing_id' => $a->id, 'title' => 'Rascunho']);

        $this->getJson('/api/announcements')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.title', 'Aviso B') // mais recente primeiro
            ->assertJsonPath('data.0.issuing.id', $b->id);

        $this->getJson("/api/announcements?issuing_id={$a->id}")
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Aviso A');
    }
}
