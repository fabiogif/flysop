<?php

namespace Database\Seeders;

use App\Models\TypeOccurrence;
use Illuminate\Database\Seeder;

class TypeOccurrenceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        return TypeOccurrence::factory()->count(2)->create();
    }
}
