<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reservation;
use App\Models\ParkingSpace;
use App\Models\ParkingType;
use Carbon\Carbon;

class ReservationSeeder extends Seeder
{
    public function run()
    {
        // Find a parking space to make a reservation (assuming you have a way to fetch or create ParkingSpace instances)
        $parkingSpace = ParkingSpace::first(); // Adjust as per your logic to fetch or create parking spaces
        $parkingType = ParkingType::all(); // Adjust as per your logic to fetch or create parking spaces
        // dd($parkingSpace);
        // Specify the start and end times for the reservation
        $start_time = '2024-05-24T10:00:00';
        $end_time = '2024-05-24T12:00:00';
        foreach ($parkingType as $data) {
            // Create a reservation for the parking space
            Reservation::create([
                'user_id' => 1, // Adjust with the user ID or logic to assign users
                'parking_space_id' => $parkingSpace->id,
                'parking_type_id' => $data['id'],
                'start_time' => Carbon::parse($start_time),
                'end_time' => Carbon::parse($end_time),
            ]);
        }

        // You can create more reservations here with different start and end times to test overlapping scenarios
    }
}
