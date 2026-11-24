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
        foreach (Issuing::DEFAULT_NAMES as $name) {
            Issuing::firstOrCreate(['name' => $name]);
        }
    }
}
