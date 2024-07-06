<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ParkingSpace;
use App\Models\ParkingType;
use App\Models\Reservation;
use Carbon\Carbon;

class ReservationSeeder extends Seeder
{
    public function run()
    {
        // Find or create parking space and parking types (assuming you have a way to fetch or create ParkingSpace instances)
        $parkingSpace = ParkingSpace::first(); // Adjust as per your logic to fetch or create parking spaces
        $parkingTypes = ParkingType::all(); // Adjust as per your logic to fetch or create parking types

        if (!$parkingSpace || $parkingTypes->isEmpty()) {
            return;
            // Ensure you have at least one parking space and three parking types
           // throw new Exception('You must have at least one parking space and three parking types in the database.');
        }

        // Specify the start and end times for the reservation
        $start_time = '2024-05-24T10:00:00';
        $end_time = '2024-05-24T12:00:00';

        foreach ($parkingTypes as $parkingType) {
            // Create a reservation for each parking type
            Reservation::create([
                'user_id' => 1,
                'parking_space_id' => $parkingSpace->id,
                'parking_type_id' => $parkingType->id,
                'start_time' => Carbon::parse($start_time),
                'end_time' => $parkingType->name === 'Monthly' ? '7 days a week' : Carbon::parse($end_time),
                'price' => $parkingType->pre_approval_required ? null : 100,
                'status' => $parkingType->pre_approval_required ? "Pending" : "Approved",
            ]);
        }
    }
}
