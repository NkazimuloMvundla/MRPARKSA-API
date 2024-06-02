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
use Illuminate\Validation\ValidationException; // Import ValidationException
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\JsonResponse; // Ensure this import is presen
use App\Models\Review;
use App\Models\ReviewAspect;
use App\Models\ReviewAspectRating;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class ParkingController extends Controller
{
    public function createParkingSpace(Request $request)
    {
        dd($request);

        $validated = Validator::make($request->all(), [
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
            'pre_approval_required' => 'boolean',
            'cancellation_policy' => 'sometimes|string',
            'access_hours' => 'sometimes|array',
            'things_to_know' => 'sometimes|string',
            'how_to_redeem' => 'sometimes|string',
        ]);

        if ($validated->fails()) {
            return response()->json(['errors' => $validated->errors()], 422);
        }
        // $validated['access_hours'] = json_encode($validated['access_hours'] ?? []);

        $parkingSpace = ParkingSpace::create([
            'user_id' => Auth::id(),
            'type' => $validated['type'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'address' => $validated['address'],
            'description' => $validated['description'],
            'capacity' => $validated['capacity'],
            'contact_info' => $validated['contact_info'],
            'amenities' => $validated['amenities'],
            'pre_approval_required' => $validated['pre_approval_required'] ?? false,
            'cancellation_policy' => $validated['cancellation_policy'],
            'access_hours' => json_encode($validated['access_hours'] ?? []),
            'things_to_know' => $validated['things_to_know'],
            'how_to_redeem' => $validated['how_to_redeem'],
        ]);

        if ($request->has('images')) {
            foreach ($request->images as $imageBase64) {
                ParkingSpacePicture::create([
                    'parking_space_id' => $parkingSpace->id,
                    'image_base64' => $imageBase64,
                ]);
            }
        }

        // Attach types to the parking space
        foreach ($validated['types'] as $typeId) {
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

    public function findParking(Request $request)
    {
        try {
            // Validate the incoming request
            $validated = $request->validate([
                'type' => ['required', 'numeric', 'max:255'], // hourly, monthly, or airport
                'latitude' => 'required|numeric', // e.g., 37.7749
                'longitude' => 'required|numeric', // e.g., -122.4194
                'start_time' => 'required|string', // e.g., 2024-02-03 10:00:00
                'end_time' => 'required|string|after:start_time', // e.g., 2024-02-03 12:00:00
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
            //dd($boundingBox);
            $nearbyParkingSpaces = ParkingSpace::selectRaw("
            *,
            (6371 * acos(
                cos(radians($latitude)) *
                cos(radians(latitude)) *
                cos(radians(longitude) - radians($longitude)) +
                sin(radians($latitude)) *
                sin(radians(latitude))
            )) AS distance")
                ->having('distance', '<', $radius)
                ->whereBetween('latitude', [$boundingBox['min_lat'], $boundingBox['max_lat']])
                ->whereBetween('longitude', [$boundingBox['min_lng'], $boundingBox['max_lng']])
                ->whereHas('types', function ($query) use ($type) {
                    $query->where('parking_space_types.parking_type_id', $type);  // Assuming 'parking_types' is the alias for your parking types table
                })
                ->whereDoesntHave('reservations', function ($query) use ($start_time, $end_time) {
                    $query->where(function ($query) use ($start_time, $end_time) {
                        $query->whereBetween('start_time', [$start_time, $end_time])
                            ->orWhereBetween('end_time', [$start_time, $end_time]);
                    });
                })
                ->get();

            // Return the result as JSON response
            return response()->json($nearbyParkingSpaces);
        } catch (ValidationException $e) {
            // Return validation error response
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Handle other exceptions and return an appropriate response
            return response()->json(['message' => 'Server error', 'error' => $e->getMessage()], 500);
        }
    }


    private function calculateBoundingBox($latitude, $longitude, $radius)
    {
        $earthRadius = 6371; // Earth radius in kilometers

        // Calculate latitude boundaries
        $maxLat = $latitude + rad2deg($radius / $earthRadius);
        $minLat = $latitude - rad2deg($radius / $earthRadius);

        // Calculate longitude boundaries
        $maxLng = $longitude + rad2deg($radius / $earthRadius / cos(deg2rad($latitude)));
        $minLng = $longitude - rad2deg($radius / $earthRadius / cos(deg2rad($latitude)));

        // Ensure correct ordering for latitude and longitude boundaries
        return [
            'min_lat' => $minLat,
            'max_lat' => $maxLat,
            'min_lng' => $minLng,
            'max_lng' => $maxLng,
        ];
    }


    public function deleteAllUsers()
    {
        // ... existing code ...
    }



    // Get details of a specific parking space
    public function getParkingSpace($id)
    {
        $parkingSpace = ParkingSpace::with(['types', 'pictures', 'reservations'])->findOrFail($id);
        return response()->json($parkingSpace);
    }

    // Update a specific parking space
    public function updateParkingSpace(Request $request, $id)
    {
        $validated = $request->validate([
            'type' => 'nullable|numeric|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'capacity' => 'nullable|integer',
            'contact_info' => 'nullable|string',
            'amenities' => 'nullable|array',
            'rating' => 'nullable|numeric|between:1,5',
            'pre_approval_required' => 'boolean',
        ]);

        $parkingSpace = ParkingSpace::findOrFail($id);
        $parkingSpace->update($validated);
        return response()->json(['message' => 'Parking space updated successfully', 'parking_space' => $parkingSpace]);
    }

    // Delete a specific parking space
    public function deleteParkingSpace($id)
    {
        $parkingSpace = ParkingSpace::findOrFail($id);
        $parkingSpace->delete();
        return response()->json(['message' => 'Parking space deleted successfully']);
    }

    // List available parking types
    public function listParkingTypes()
    {
        $parkingTypes = ParkingType::all();
        return response()->json($parkingTypes);
    }

    public function getMyParkingSpaces()
    {
        $parkingSpaces = ParkingSpace::where('user_id', Auth::id())->with(['types', 'pictures', 'reservations'])->get();
        return response()->json($parkingSpaces);
    }

    /**
     * Assign an admin to a parking space.
     */
    // public function assignAdmin(Request $request)
    // {
    //     // Find the parking space
    //     $parkingSpace = ParkingSpace::findOrFail(1);

    //     // Check if the user is authorized based on the policy
    //     // if (Gate::denies('assignAdmin', $parkingSpace)) {
    //     //     return response()->json(['message' => 'Unauthorized'], 403);
    //     // }

    //     // Validate the request
    //     $request->validate([
    //         'email' => 'required|email|exists:users,email',
    //     ]);

    //     // Find the user by email
    //     $user = User::where('email', $request->email)->firstOrFail();

    //     // Attach the user as an admin to the parking space
    //     $parkingSpace->admins()->attach($user);

    //     // Return success response
    //     return response()->json(['message' => 'Admin assigned successfully'], 200);
    // }

    public function assignAdmin(Request $request)
    {
        try {
            // Find the parking space
            $parkingSpace = ParkingSpace::findOrFail(1);

            // Check if the user is authorized based on the policy
            // if (Gate::denies('assignAdmin', $parkingSpace)) {
            //     return response()->json(['message' => 'Unauthorized'], 403);
            // }

            // Validate the request
            $request->validate([
                'email' => 'required|email|exists:users,email',
                'parking_space_id' => 'required|numeric',
            ]);

            // Find the user by email
            $user = User::where('email', $request->email)->firstOrFail();

            // Attach the user as an admin to the parking space
            $parkingSpace->admins()->attach($user);

            return response()->json(['message' => 'Admin assigned successfully'], 200);
        } catch (ValidationException $e) {
            // Handle the case where user with specified email is not found
            return response()->json(['message' => 'User not found'], 404);
        }
        catch (\Exception $e) {
            // Handle other exceptions and return an appropriate response
            return response()->json(['message' => 'Server error', 'error' => $e->getMessage()], 500);
        }
    }
}
