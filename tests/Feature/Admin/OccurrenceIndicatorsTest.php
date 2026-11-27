<?php

namespace Tests\Feature\Admin;

use App\Models\Issuing;
use App\Models\Occurrences;
use App\Models\OccurrenceStatusHistory;
use App\Models\Priority;
use App\Models\StatusOccurrence;
use App\Models\Tenant;
use App\Models\User;
use App\Services\DashboardService;
use Database\Seeders\PrioritySeeder;
use Database\Seeders\StatusOccurrenceDriverSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/**
 * Eixo 4 — Fase A: indicadores, priorização por gravidade, filtro por bairro e status Indeferida.
 */
class OccurrenceIndicatorsTest extends TestCase
{
    use RefreshDatabase;

    private StatusOccurrence $aberta;
    private StatusOccurrence $finalizada;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('acl.admin', ['admin-indicators@flysop.test']);
        $this->seed(StatusOccurrenceDriverSeeder::class);
        $this->aberta = StatusOccurrence::where('name', 'Recebida')->first();
        $this->finalizada = StatusOccurrence::where('name', 'Finalizada')->first();
    }

    private function actingAsAdmin(): User
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'email' => 'admin-indicators@flysop.test']);
        $this->actingAs($user);

        return $user;
    }

    public function test_indeferida_is_a_terminal_status(): void
    {
        $this->assertTrue(StatusOccurrence::where('name', 'Indeferida')->value('is_terminal'));
    }

    public function test_indicators_count_concluded_late_average_time_and_rankings(): void
    {
        $this->actingAsAdmin();
        $saude = Issuing::factory()->create(['name' => 'Secretaria de Saúde']);

        $done = Occurrences::factory()->create([
            'status_occurrences_id' => $this->finalizada->id,
            'issuings_id' => $saude->id,
            'neighborhood' => 'Centro',
            'created_at' => Carbon::parse('2026-11-01 08:00:00'),
        ]);
        // Primeira conclusão 10h depois; a segunda (após reabertura) não conta.
        OccurrenceStatusHistory::forceCreate(['occurrence_id' => $done->id, 'to_status_id' => $this->finalizada->id, 'created_at' => Carbon::parse('2026-11-01 18:00:00')]);
        OccurrenceStatusHistory::forceCreate(['occurrence_id' => $done->id, 'to_status_id' => $this->finalizada->id, 'created_at' => Carbon::parse('2026-11-05 18:00:00')]);

        Occurrences::factory()->count(2)->create([
            'status_occurrences_id' => $this->aberta->id,
            'issuings_id' => $saude->id,
            'neighborhood' => 'Pituba',
            'due_at' => now()->subHour(),
        ]);

        $indicators = app(DashboardService::class)->getIndicators();

        $this->assertSame(1, $indicators['concluidas']);
        $this->assertSame(2, $indicators['atrasadas']);
        $this->assertSame(10.0, (float) $indicators['tempoMedioHoras']);
        $this->assertSame('Pituba', $indicators['porBairro'][0]->name);
        $this->assertSame(2, (int) $indicators['porBairro'][0]->abertas);
        $this->assertSame('Secretaria de Saúde', $indicators['porSecretaria'][0]->name);
        $this->assertSame(3, (int) $indicators['porSecretaria'][0]->total);

        $this->get(route('admin.index'))->assertOk()->assertSee('Regiões críticas (por bairro)')->assertSee('10,0 h');
    }

    public function test_gravity_order_puts_open_and_heavier_priority_first(): void
    {
        $this->seed(PrioritySeeder::class);
        $critica = Priority::where('name', 'Crítica')->first();
        $baixa = Priority::where('name', 'Baixa')->first();

        $closedCritical = Occurrences::factory()->create(['status_occurrences_id' => $this->finalizada->id, 'priority_id' => $critica->id]);
        $openLow = Occurrences::factory()->create(['status_occurrences_id' => $this->aberta->id, 'priority_id' => $baixa->id]);
        $openNone = Occurrences::factory()->create(['status_occurrences_id' => $this->aberta->id, 'priority_id' => null]);
        $openCritical = Occurrences::factory()->create(['status_occurrences_id' => $this->aberta->id, 'priority_id' => $critica->id]);

        $this->assertSame(
            [$openCritical->id, $openLow->id, $openNone->id, $closedCritical->id],
            Occurrences::orderByGravity()->pluck('id')->all()
        );
    }

    public function test_dashboard_map_filters_by_neighborhood(): void
    {
        $this->actingAsAdmin();
        $centro = Occurrences::factory()->create(['status_occurrences_id' => $this->aberta->id, 'neighborhood' => 'Centro']);
        Occurrences::factory()->create(['status_occurrences_id' => $this->aberta->id, 'neighborhood' => 'Pituba']);

        $this->getJson(route('admin.dashboard.occurrences-recent', ['neighborhood' => 'Centro']))
            ->assertOk()
            ->assertJsonCount(1, 'occurrences')
            ->assertJsonPath('occurrences.0.id', $centro->id);

        $this->getJson(route('admin.dashboard.occurrences-heatmap', ['neighborhood' => 'Centro']))
            ->assertOk()
            ->assertJsonCount(1, 'points');
    }
}
