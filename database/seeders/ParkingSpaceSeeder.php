<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ParkingSpace;
use App\Models\ParkingSpacePicture;
use App\Models\ParkingSpaceType;
use App\Models\ParkingSpacePrice;
use App\Models\ParkingType;

class ParkingSpaceSeeder extends Seeder
{
    public function run()
    {
       // Fetch existing parking types
       $hourly = ParkingType::where('name', 'hourly')->first();
       $monthly = ParkingType::where('name', 'monthly')->first();
       $airport = ParkingType::where('name', 'airport')->first();

        // Create parking spaces with their respective relationships
        $parkingSpaces = [
            [
                'latitude' => -29.8587,
                'longitude' => 31.0218,
                'address' => '123 Beach Rd, Durban, South Africa',
                'description' => 'Secure parking space near the beach',
                'capacity' => 50,
                'contact_info' => 'contact@example.com',
                'amenities' => ['CCTV', '24/7 Access'],
                'images' => [
                    'data:image/jpeg;base64,' . base64_encode(file_get_contents(public_path('images/park1.jpg'))),
                    'data:image/jpeg;base64,' . base64_encode(file_get_contents(public_path('images/park2.jpg'))),
                ],
                'types' => [
                    ['type_id' => $hourly->id, 'price' => 5.00],
                    ['type_id' => $monthly->id, 'price' => 150.00],
                ],
            ],
            // Add more parking spaces as needed
        ];

        foreach ($parkingSpaces as $data) {
            $parkingSpace = ParkingSpace::create([
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'address' => $data['address'],
                'description' => $data['description'],
                'capacity' => $data['capacity'],
                'contact_info' => $data['contact_info'],
                'amenities' => json_encode($data['amenities']),
                'rating' => '2.0'
            ]);

            // Save images
            foreach ($data['images'] as $imageBase64) {
                ParkingSpacePicture::create([
                    'parking_space_id' => $parkingSpace->id,
                    'image_base64' => $imageBase64,
                ]);
            }

            // Save types and prices
            foreach ($data['types'] as $type) {
                ParkingSpaceType::create([
                    'parking_space_id' => $parkingSpace->id,
                    'parking_type_id' => $type['type_id'],
                ]);

                ParkingSpacePrice::create([
                    'parking_space_id' => $parkingSpace->id,
                    'parking_type_id' => $type['type_id'],
                    'price' => $type['price'],
                ]);
            }
        }
    }
}
