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
                'things_to_know' => 'Parking is at your own risk.',
                'how_to_redeem' => 'Show your reservation at the entrance.',
                'cancellation_policy' => 'Free cancellation up to 24 hours before reservation.',
                'access_hours' => json_encode([
                    'monday' => ['open' => '08:00', 'close' => '20:00'],
                    'tuesday' => ['open' => '08:00', 'close' => '20:00'],
                    // add other days
                ]),
                'user_id' => 1,
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

            [
                'user_id' => 1,
                'latitude' => -29.8637, // Approximately 5km away
                'longitude' => 31.0268, // Approximately 5km away
                'user_id' => 1,
                'address' => '456 City Center Rd, Durban, South Africa',
                'description' => 'Parking space in the city center',
                'capacity' => 30,
                'contact_info' => 'citycenter@example.com',
                'amenities' => ['Gated', 'Security Guard'],
                'things_to_know' => 'Parking is at your own risk.',
                'how_to_redeem' => 'Show your reservation at the entrance.',
                'cancellation_policy' => 'Free cancellation up to 24 hours before reservation.',
                'access_hours' => json_encode([
                    'monday' => ['open' => '08:00', 'close' => '20:00'],
                    'tuesday' => ['open' => '08:00', 'close' => '20:00'],
                    // add other days
                ]),
                'images' => [
                    'data:image/jpeg;base64,' . base64_encode(file_get_contents(public_path('images/park3.webp'))),
                    'data:image/jpeg;base64,' . base64_encode(file_get_contents(public_path('images/park4.webp'))),
                ],
                'types' => [
                    ['type_id' => $airport->id, 'price' => 100.00],
                ],
            ],
            [
                'user_id' => 1,
                'latitude' => -29.8000, // Adjust the latitude to be over 5km away
                'longitude' => 31.1000, // Adjust the longitude to be over 5km away
                'address' => '789 Park Ave, Durban, South Africa',
                'description' => 'Remote parking space away from city center',
                'capacity' => 20,
                'contact_info' => 'contact@example.com',
                'amenities' => ['Remote Location', 'Easy Access'],
                'things_to_know' => 'Parking is at your own risk.',
                'how_to_redeem' => 'Show your reservation at the entrance.',
                'cancellation_policy' => 'Free cancellation up to 24 hours before reservation.',
                'access_hours' => json_encode([
                    'monday' => ['open' => '08:00', 'close' => '20:00'],
                    'tuesday' => ['open' => '08:00', 'close' => '20:00'],
                    // add other days
                ]),
                'images' => [
                    'data:image/jpeg;base64,' . base64_encode(file_get_contents(public_path('images/park4.webp'))),
                ],
                'types' => [
                    ['type_id' => $hourly->id, 'price' => 6.00],
                ],
            ],
        ];

        foreach ($parkingSpaces as $data) {
            $parkingSpace = ParkingSpace::create([
                'user_id' => 1,
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'address' => $data['address'],
                'description' => $data['description'],
                'capacity' => $data['capacity'],
                'contact_info' => $data['contact_info'],
                'amenities' => json_encode($data['amenities']),
                'rating' => '5.0',

                'things_to_know' => $data['things_to_know'],
                'how_to_redeem' => $data['how_to_redeem'],
                'cancellation_policy' => $data['cancellation_policy'],
                'description' => $data['description'],
                'capacity' => $data['capacity'],
                'contact_info' => $data['contact_info'],
                'access_hours' => json_encode([
                    'monday' => ['open' => '08:00', 'close' => '20:00'],
                    'tuesday' => ['open' => '08:00', 'close' => '20:00'],
                    // add other days
                ]),
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
