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

        return response()->json($parkingSpace, 201);
    }

    public function findParking(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:hourly,monthly,airport',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'radius' => 'required|numeric', // Radius in kilometers
        ]);

        $type = $validated['type'];
        $latitude = $validated['latitude'];
        $longitude = $validated['longitude'];
        $startTime = $validated['start_time'];
        $endTime = $validated['end_time'];
        $radius = $validated['radius'];

        // Retrieve available parking spaces within the specified radius and type
        $availableParkingSpaces = ParkingSpace::where('type', $type)
            ->whereRaw("(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) < ?", [
                $latitude, $longitude, $latitude, $radius
            ])
            ->whereDoesntHave('reservations', function ($query) use ($startTime, $endTime) {
                $query->where(function ($query) use ($startTime, $endTime) {
                    $query->whereBetween('start_time', [$startTime, $endTime])
                          ->orWhereBetween('end_time', [$startTime, $endTime])
                          ->orWhere(function ($query) use ($startTime, $endTime) {
                              $query->where('start_time', '<=', $startTime)
                                    ->where('end_time', '>=', $endTime);
                          });
                });
            })
            ->get();

        return response()->json($availableParkingSpaces);
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
