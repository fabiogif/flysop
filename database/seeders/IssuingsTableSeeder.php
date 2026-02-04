<?php

namespace Database\Seeders;

use App\Models\Issuing;
use Illuminate\Database\Seeder;

class IssuingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        return Issuing::factory()->count(10)->create();
    }
}
