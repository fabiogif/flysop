<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_all_states_and_cities_of_a_state(): void
    {
        $this->getJson('/api/states')
            ->assertOk()
            ->assertJsonCount(27, 'data')
            ->assertJsonFragment(['uf' => 'BA', 'name' => 'Bahia']);

        $this->getJson('/api/states/ba/cities')
            ->assertOk()
            ->assertJsonFragment(['id' => 2927408, 'name' => 'Salvador'])
            ->assertJsonMissing(['name' => 'São Paulo']);

        $this->assertDatabaseCount('cities', 5571);
    }

    public function test_unknown_state_returns_empty_list(): void
    {
        $this->getJson('/api/states/XX/cities')->assertOk()->assertJsonCount(0, 'data');
    }
}
