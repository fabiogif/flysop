<?php

namespace Tests\Unit\Services;

use App\Models\Occurrences;
use App\Models\Priority;
use App\Models\StatusOccurrence;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardChartsDataTest extends TestCase
{
    use RefreshDatabase;

    private DashboardService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(DashboardService::class);
        Carbon::setTestNow(Carbon::parse('2026-08-10 15:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_by_day_returns_exactly_14_days_ending_today(): void
    {
        $charts = $this->service->getChartsData();

        $this->assertArrayHasKey('byDay', $charts);
        $this->assertCount(14, $charts['byDay']);
        $this->assertSame(array_keys($charts['byDay'])[0], '2026-07-28');
        $this->assertSame(array_keys($charts['byDay'])[13], '2026-08-10');
        $this->assertSame(array_values($charts['byDay']), array_fill(0, 14, 0));
    }

    public function test_by_day_counts_occurrences_in_last_14_days_and_ignores_older(): void
    {
        Occurrences::factory()->create([
            'created_at' => Carbon::parse('2026-08-10 09:00:00'),
            'updated_at' => Carbon::parse('2026-08-10 09:00:00'),
        ]);
        Occurrences::factory()->create([
            'created_at' => Carbon::parse('2026-08-10 18:00:00'),
            'updated_at' => Carbon::parse('2026-08-10 18:00:00'),
        ]);
        Occurrences::factory()->create([
            'created_at' => Carbon::parse('2026-07-28 12:00:00'),
            'updated_at' => Carbon::parse('2026-07-28 12:00:00'),
        ]);
        // Fora da janela (15º dia atrás / anterior a since = subDays(13))
        Occurrences::factory()->create([
            'created_at' => Carbon::parse('2026-07-27 23:59:59'),
            'updated_at' => Carbon::parse('2026-07-27 23:59:59'),
        ]);

        $charts = $this->service->getChartsData();

        $this->assertSame(2, $charts['byDay']['2026-08-10']);
        $this->assertSame(1, $charts['byDay']['2026-07-28']);
        $this->assertSame(0, $charts['byDay']['2026-07-29']);
        $this->assertSame(3, array_sum($charts['byDay']));
    }

    public function test_by_status_groups_and_orders_by_sort_order(): void
    {
        $aberto = StatusOccurrence::factory()->create([
            'name' => 'Aberto',
            'sort_order' => 10,
            'is_terminal' => false,
        ]);
        $fechado = StatusOccurrence::factory()->create([
            'name' => 'Fechado',
            'sort_order' => 20,
            'is_terminal' => true,
        ]);
        $emAtendimento = StatusOccurrence::factory()->create([
            'name' => 'Em atendimento',
            'sort_order' => 15,
            'is_terminal' => false,
        ]);

        Occurrences::factory()->count(3)->create(['status_occurrences_id' => $aberto->id]);
        Occurrences::factory()->count(1)->create(['status_occurrences_id' => $fechado->id]);
        Occurrences::factory()->count(2)->create(['status_occurrences_id' => $emAtendimento->id]);

        $charts = $this->service->getChartsData();
        $byStatus = $charts['byStatus']->toArray();

        $this->assertSame(['Aberto', 'Em atendimento', 'Fechado'], array_keys($byStatus));
        $this->assertSame(3, (int) $byStatus['Aberto']);
        $this->assertSame(2, (int) $byStatus['Em atendimento']);
        $this->assertSame(1, (int) $byStatus['Fechado']);
    }

    public function test_by_priority_groups_with_color_and_orders_by_total_desc(): void
    {
        $alta = Priority::factory()->create([
            'name' => 'Alta',
            'color' => '#e74c3c',
            'weight' => 90,
        ]);
        $baixa = Priority::factory()->create([
            'name' => 'Baixa',
            'color' => '#2ecc71',
            'weight' => 10,
        ]);

        Occurrences::factory()->count(1)->create(['priority_id' => $alta->id]);
        Occurrences::factory()->count(4)->create(['priority_id' => $baixa->id]);
        // Sem prioridade: não entra no gráfico (join interno)
        Occurrences::factory()->create(['priority_id' => null]);

        $charts = $this->service->getChartsData();
        $byPriority = $charts['byPriority']->values()->all();

        $this->assertCount(2, $byPriority);
        $this->assertSame('Baixa', $byPriority[0]['name']);
        $this->assertSame(4, $byPriority[0]['total']);
        $this->assertSame('#2ecc71', $byPriority[0]['color']);
        $this->assertSame('Alta', $byPriority[1]['name']);
        $this->assertSame(1, $byPriority[1]['total']);
        $this->assertSame('#e74c3c', $byPriority[1]['color']);
    }
}
