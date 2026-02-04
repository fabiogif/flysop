<?php

namespace Database\Seeders;

use App\Models\Occurrences;
use Illuminate\Database\Seeder;

class OccurrenceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        return Occurrences::factory()->count(10)->create();
    }
}
