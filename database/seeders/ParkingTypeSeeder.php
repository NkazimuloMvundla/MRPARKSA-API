<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ParkingType;

class ParkingTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = ['hourly', 'monthly', 'airport'];

        foreach ($types as $type) {
            ParkingType::create(['name' => $type]);
        }
    }
}
