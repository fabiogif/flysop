<?php

namespace Tests\Feature\Admin;

use App\Models\Driver;
use App\Models\DriverPosition;
use App\Models\Occurrences;
use App\Models\Priority;
use App\Models\StatusOccurrence;
use App\Models\Tenant;
use App\Models\TypeOccurrence;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class DispatchConsoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('acl.admin', ['admin-dispatch@flysop.test']);
    }

    private function actingAsAdmin(): User
    {
        $tenant = Tenant::factory()->create(['name' => 'Org Dispatch']);
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'admin-dispatch@flysop.test',
        ]);

        $this->actingAs($user);

        return $user;
    }

    public function test_console_renders_for_admin(): void
    {
        $this->actingAsAdmin();

        $response = $this->get(route('dispatch.console'));

        $response->assertOk();
        $response->assertSee('Central de Despacho', false);
        $response->assertSee('dispatch-map', false);
        $response->assertSee('data-chip="critical"', false);
        $response->assertSee('data-chip="no_driver"', false);
    }

    public function test_occurrences_recent_supports_open_only_and_no_driver_chips(): void
    {
        $this->actingAsAdmin();

        $open = StatusOccurrence::factory()->create(['name' => 'Recebida', 'is_terminal' => false, 'sort_order' => 1]);
        $terminal = StatusOccurrence::factory()->create(['name' => 'Finalizada', 'is_terminal' => true, 'sort_order' => 100]);

        $driver = Driver::create([
            'name' => 'Motorista Teste',
            'status' => Driver::STATUS_DISPONIVEL,
            'tenant_id' => auth()->user()->tenant_id,
        ]);

        $openNoDriver = Occurrences::factory()->create(['status_occurrences_id' => $open->id, 'driver_id' => null]);
        $openWithDriver = Occurrences::factory()->create(['status_occurrences_id' => $open->id, 'driver_id' => $driver->id]);
        $closed = Occurrences::factory()->create(['status_occurrences_id' => $terminal->id, 'driver_id' => null]);

        $openOnly = $this->getJson(route('admin.dashboard.occurrences-recent', ['open_only' => 1, 'limit' => 50]))->json('occurrences');
        $ids = collect($openOnly)->pluck('id')->all();
        $this->assertContains($openNoDriver->id, $ids);
        $this->assertContains($openWithDriver->id, $ids);
        $this->assertNotContains($closed->id, $ids);

        $noDriver = $this->getJson(route('admin.dashboard.occurrences-recent', ['no_driver' => 1, 'limit' => 50]))->json('occurrences');
        $ids = collect($noDriver)->pluck('id')->all();
        $this->assertContains($openNoDriver->id, $ids);
        $this->assertContains($closed->id, $ids);
        $this->assertNotContains($openWithDriver->id, $ids);
    }

    public function test_heatmap_respects_priority_filter(): void
    {
        $this->actingAsAdmin();

        $status = StatusOccurrence::factory()->create(['name' => 'Recebida', 'sort_order' => 1]);
        $critical = Priority::factory()->create(['name' => 'Crítica', 'weight' => 100]);
        $low = Priority::factory()->create(['name' => 'Baixa', 'weight' => 10]);

        Occurrences::factory()->create([
            'status_occurrences_id' => $status->id,
            'priority_id' => $critical->id,
            'latitude' => -12.9, 'longitude' => -38.4,
        ]);
        Occurrences::factory()->create([
            'status_occurrences_id' => $status->id,
            'priority_id' => $low->id,
            'latitude' => -12.8, 'longitude' => -38.3,
        ]);

        $points = $this->getJson(route('admin.dashboard.occurrences-heatmap', ['priority_id' => $critical->id]))
            ->json('points');

        $this->assertCount(1, $points);
    }

    public function test_assign_driver_sets_driver_and_moves_to_awaiting_acceptance(): void
    {
        $user = $this->actingAsAdmin();

        $status = StatusOccurrence::factory()->create(['name' => 'Recebida', 'is_terminal' => false, 'sort_order' => 1]);
        $awaiting = StatusOccurrence::factory()->create(['name' => 'Aguardando aceitação', 'is_terminal' => false, 'sort_order' => 50]);
        $occurrence = Occurrences::factory()->create(['status_occurrences_id' => $status->id, 'driver_id' => null]);
        $driver = Driver::create([
            'name' => 'Motorista Atribuído',
            'status' => Driver::STATUS_DISPONIVEL,
            'tenant_id' => $user->tenant_id,
        ]);

        $response = $this->postJson(route('occurrences.assign-driver', $occurrence->id), ['driver_id' => $driver->id]);

        $response->assertOk();
        $occurrence->refresh();
        $this->assertSame($driver->id, $occurrence->driver_id);
        $this->assertSame($awaiting->id, $occurrence->status_occurrences_id);
    }

    public function test_suggest_drivers_returns_candidate_coordinates(): void
    {
        $user = $this->actingAsAdmin();

        $type = TypeOccurrence::factory()->create();
        $status = StatusOccurrence::factory()->create(['name' => 'Recebida', 'sort_order' => 1]);
        $occurrence = Occurrences::factory()->create([
            'status_occurrences_id' => $status->id,
            'type_occurrences_id' => $type->id,
            'latitude' => -12.9530,
            'longitude' => -38.4970,
        ]);

        $driver = Driver::create([
            'name' => 'Motorista Próximo',
            'status' => Driver::STATUS_DISPONIVEL,
            'tenant_id' => $user->tenant_id,
        ]);
        DriverPosition::create([
            'driver_id' => $driver->id,
            'latitude' => -12.9535,
            'longitude' => -38.4975,
        ]);

        $response = $this->getJson(route('occurrences.suggest-drivers', $occurrence->id));

        $response->assertOk();
        $drivers = $response->json('drivers');
        $this->assertCount(1, $drivers);
        $this->assertArrayHasKey('latitude', $drivers[0]);
        $this->assertArrayHasKey('longitude', $drivers[0]);
        $this->assertNotNull($drivers[0]['latitude']);
        $this->assertNotNull($drivers[0]['longitude']);
    }
}
