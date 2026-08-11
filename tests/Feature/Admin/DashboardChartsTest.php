<?php

namespace Tests\Feature\Admin;

use App\Models\Occurrences;
use App\Models\Priority;
use App\Models\StatusOccurrence;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class DashboardChartsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-08-10 15:00:00'));
        Config::set('acl.admin', ['admin-charts@flysop.test']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function actingAsAdmin(): User
    {
        $tenant = Tenant::factory()->create(['name' => 'Org Charts']);
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'admin-charts@flysop.test',
        ]);

        $this->actingAs($user);

        return $user;
    }

    public function test_dashboard_exposes_chart_sections_and_json_payload(): void
    {
        $this->actingAsAdmin();

        $status = StatusOccurrence::factory()->create([
            'name' => 'Aberto',
            'sort_order' => 1,
        ]);
        $priority = Priority::factory()->create([
            'name' => 'Alta',
            'color' => '#c0392b',
        ]);

        Occurrences::factory()->count(2)->create([
            'status_occurrences_id' => $status->id,
            'priority_id' => $priority->id,
            'created_at' => Carbon::parse('2026-08-09 10:00:00'),
            'updated_at' => Carbon::parse('2026-08-09 10:00:00'),
        ]);

        $response = $this->get(route('admin.index'));

        $response->assertOk();
        $response->assertSee('Ocorrências por dia (últimos 14 dias)', false);
        $response->assertSee('Por status', false);
        $response->assertSee('Por prioridade', false);
        $response->assertSee('chart-occurrences-by-day', false);
        $response->assertSee('chart-occurrences-by-status', false);
        $response->assertSee('chart-occurrences-by-priority', false);
        $response->assertSee('window.dashboardChartsData', false);
        $response->assertSee('2026-08-09', false);
        $response->assertSee('Aberto', false);
        $response->assertSee('Alta', false);
        $response->assertSee('#c0392b', false);
    }

    public function test_dashboard_charts_data_matches_service_aggregation(): void
    {
        $this->actingAsAdmin();

        $statusA = StatusOccurrence::factory()->create(['name' => 'Status A', 'sort_order' => 1]);
        $statusB = StatusOccurrence::factory()->create(['name' => 'Status B', 'sort_order' => 2]);
        $priority = Priority::factory()->create(['name' => 'Média', 'color' => '#f1c40f']);

        Occurrences::factory()->create([
            'status_occurrences_id' => $statusA->id,
            'priority_id' => $priority->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Occurrences::factory()->create([
            'status_occurrences_id' => $statusB->id,
            'priority_id' => $priority->id,
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        $response = $this->get(route('admin.index'));
        $response->assertOk();

        $charts = $response->viewData('charts');

        $this->assertCount(14, $charts['byDay']);
        $this->assertSame(1, $charts['byDay'][now()->format('Y-m-d')]);
        $this->assertSame(1, $charts['byDay'][now()->subDays(2)->format('Y-m-d')]);
        $this->assertSame(1, (int) $charts['byStatus']['Status A']);
        $this->assertSame(1, (int) $charts['byStatus']['Status B']);
        $this->assertCount(1, $charts['byPriority']);
        $this->assertSame('Média', $charts['byPriority'][0]['name']);
        $this->assertSame(2, $charts['byPriority'][0]['total']);
    }
}
