<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Driver;
use App\Models\Occurrences;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use App\Events\DriverPositionUpdated;
use App\Models\Category;
use App\Models\TypeOccurrence;
use App\Models\Client;

class DriverLocationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
    }

    public function test_driver_can_emit_location_without_occurrence()
    {
        $tenant = Tenant::factory()->create();
        $driver = Driver::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->postJson('/api/driver/position', [
            'driver_id' => $driver->id,
            'latitude' => -23.550520,
            'longitude' => -46.633308,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'position' => ['id', 'latitude', 'longitude']]);

        $this->assertDatabaseHas('driver_positions', [
            'driver_id' => $driver->id,
            'latitude' => -23.550520,
            'longitude' => -46.633308,
            'occurrence_id' => null,
        ]);

        Event::assertNotDispatched(DriverPositionUpdated::class);
    }

    public function test_driver_can_emit_location_with_occurrence_and_triggers_event()
    {
        $tenant = Tenant::factory()->create();
        $category = Category::factory()->create(['tenant_id' => $tenant->id]);
        $type = TypeOccurrence::factory()->create(['tenant_id' => $tenant->id, 'category_id' => $category->id]);
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);

        $driver = Driver::factory()->create(['tenant_id' => $tenant->id]);
        $occurrence = Occurrences::factory()->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'type_occurrence_id' => $type->id,
            'driver_id' => $driver->id
        ]);

        $response = $this->postJson('/api/driver/position', [
            'driver_id' => $driver->id,
            'occurrence_id' => $occurrence->id,
            'latitude' => -12.9714,
            'longitude' => -38.5014,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('driver_positions', [
            'driver_id' => $driver->id,
            'occurrence_id' => $occurrence->id,
            'latitude' => -12.9714,
            'longitude' => -38.5014,
        ]);

        Event::assertDispatched(DriverPositionUpdated::class, function ($event) use ($occurrence) {
            return $event->position->occurrence_id === $occurrence->id;
        });
    }

    public function test_location_emission_fails_with_invalid_data()
    {
        $response = $this->postJson('/api/driver/position', [
            'driver_id' => 99999, // Inexistente
            'latitude' => 'invalid_lat',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['driver_id', 'latitude', 'longitude']);
    }
}
