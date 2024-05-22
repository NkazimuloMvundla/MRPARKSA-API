<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ParkingSpace;
use App\Models\ParkingSpacePicture;
use Illuminate\Support\Facades\Auth;
use App\Models\Reservation;
use Illuminate\Support\Facades\Validator;
use App\Models\ParkingSpaceType;
use App\Models\ParkingType;
use App\Models\ParkingSpacePrice;
use Illuminate\Support\Facades\DB;

class ParkingController extends Controller
{
    public function createParkingSpace(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'capacity' => 'required|integer',
            'contact_info' => 'nullable|string',
            'amenities' => 'nullable|array',
            'images' => 'nullable|array',
            'images.*' => 'string', // base64 strings are expected
            'types' => 'required|array',
            'types.*' => 'exists:parking_types,id', // Validate that each type exists
            'types.*.price' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $parkingSpace = ParkingSpace::create($validator->validated());

        if ($request->has('images')) {
            foreach ($request->images as $imageBase64) {
                ParkingSpacePicture::create([
                    'parking_space_id' => $parkingSpace->id,
                    'image_base64' => $imageBase64,
                ]);
            }
        }

         // Attach types to the parking space
         foreach ($validator['types'] as $typeId) {
            ParkingSpaceType::create([
                'parking_space_id' => $parkingSpace->id,
                'parking_type_id' => $typeId,
            ]);
        }

        foreach ($request->types as $type) {
            ParkingSpacePrice::create([
                'parking_space_id' => $parkingSpace->id,
                'parking_type_id' => $type['type_id'],
                'price' => $type['price'],
            ]);
        }

        return response()->json($parkingSpace, 201);
    }

    public function findNearbyParking(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'type' => ['required', 'string', 'max:255'], // hourly, monthly, or airport
            'latitude' => 'required|numeric', // e.g., 37.7749
            'longitude' => 'required|numeric', // e.g., -122.4194
            'start_time' => 'required|date', // e.g., 2024-02-03 10:00:00
            'end_time' => 'required|date|after:start_time', // e.g., 2024-02-03 12:00:00
        ]);

        // Define the radius (in kilometers) for the search
        $radius = 5;

        // Retrieve the necessary data from the validated input
        $type = $validated['type'];
        $latitude = $validated['latitude'];
        $longitude = $validated['longitude'];
        $start_time = $validated['start_time'];
        $end_time = $validated['end_time'];

        // Calculate the bounding box for the query
        $boundingBox = $this->calculateBoundingBox($latitude, $longitude, $radius);

        // Query to find nearby parking spaces within the calculated bounding box
        $nearbyParkingSpaces = ParkingSpace::select(
                DB::raw("*,
                    (6371 * acos(
                        cos(radians(?)) *
                        cos(radians(latitude)) *
                        cos(radians(longitude) - radians(?)) +
                        sin(radians(?)) *
                        sin(radians(latitude))
                    )) AS distance", [$latitude, $longitude, $latitude])
            )
            ->having('distance', '<', $radius)
            ->whereBetween('latitude', [$boundingBox['min_lat'], $boundingBox['max_lat']])
            ->whereBetween('longitude', [$boundingBox['min_lng'], $boundingBox['max_lng']])
            ->whereHas('types', function($query) use ($type) {
                $query->where('name', $type);
            })
            ->whereDoesntHave('reservations', function($query) use ($start_time, $end_time) {
                $query->where(function($query) use ($start_time, $end_time) {
                    $query->whereBetween('start_time', [$start_time, $end_time])
                          ->orWhereBetween('end_time', [$start_time, $end_time]);
                });
            })
            ->get();

        return response()->json($nearbyParkingSpaces);
    }

    private function calculateBoundingBox($latitude, $longitude, $radius)
    {
        $earthRadius = 6371; // Earth radius in kilometers

        $maxLat = $latitude + rad2deg($radius / $earthRadius);
        $minLat = $latitude - rad2deg($radius / $earthRadius);
        $maxLng = $longitude + rad2deg($radius / $earthRadius / cos(deg2rad($latitude)));
        $minLng = $longitude - rad2deg($radius / $earthRadius / cos(deg2rad($latitude)));

        return [
            'min_lat' => $minLat,
            'max_lat' => $maxLat,
            'min_lng' => $minLng,
            'max_lng' => $maxLng,
        ];
    }

    public function makeReservation(Request $request)
    {
        $validated = $request->validate([
            'parking_space_id' => 'required|exists:parking_spaces,id',
            'parking_type_id' => 'required|exists:parking_types,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'price' => 'required|numeric',
        ]);

        $reservation = Reservation::create([
            'user_id' => Auth::id(),
            'parking_space_id' => $validated['parking_space_id'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'price' => $validated['price'],
        ]);

        return response()->json($reservation, 201);
    }


    public function deleteAllUsers()
    {
        // ... existing code ...
    }
}
