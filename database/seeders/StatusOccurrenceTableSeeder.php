<?php

namespace Database\Seeders;

use App\Models\StatusOccurrence;
use Illuminate\Database\Seeder;

class StatusOccurrenceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        return StatusOccurrence::factory()->count(1)->create();
    }
}
