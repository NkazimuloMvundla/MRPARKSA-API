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
use App\Http\helpers\Helper;
use Carbon\Carbon;

class ParkingController extends Controller
{
    public function createParkingSpace(Request $request)
    {


        try {
            // Validate the request
            $validated = $request->validate([
                'typeIds' => 'required|array',
                'typeIds.*' => 'exists:parking_types,id',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'address' => 'required|string|max:255',
                'description' => 'nullable|string',
                'capacity' => 'nullable|numeric',
                'contact_info' => 'required|string|max:255',
                'amenities' => 'nullable|string',
                'pre_approval_required' => 'boolean',
                'cancellation_policy' => 'nullable|string',
                'access_hours' => 'required|string|max:500',
                'things_to_know' => 'nullable|string',
                'how_to_redeem' => 'nullable|string',
                'pictures' => 'nullable|array',
                'pictures.*.image_base64' => 'nullable|string',
                'close_by_airport' => 'nullable|string' // Add this line
            ]);
            // dd($request);
            // Determine if updating or creating
            if ($request->has('id') && $request->id) {
                // Limit access_hours to 500 characters before encoding
                $accessHours = substr(json_encode($validated['access_hours'] ?? []), 0, 500);
                // Updating existing parking space
                $parkingSpace = ParkingSpace::findOrFail($request->id);
                $parkingSpace->update([
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                    'address' => $validated['address'],
                    'description' => $validated['description'] ?? '',
                    'capacity' => $validated['capacity'],
                    'contact_info' => $validated['contact_info'],
                    'amenities' => $validated['amenities'] ?? '',
                    'pre_approval_required' => $validated['pre_approval_required'] ?? false,
                    'cancellation_policy' => $validated['cancellation_policy'] ?? '',
                    'access_hours' => $accessHours,  // Use the truncated access_hours value
                    'things_to_know' => $validated['things_to_know'] ?? '',
                    'how_to_redeem' => $validated['how_to_redeem'] ?? '',
                    'close_by_airport' => $validated['close_by_airport'],
                ]);

                // Remove existing images and replace with new ones
                $parkingSpace->pictures()->delete();
            } else {
                $accessHours = substr(json_encode($validated['access_hours'] ?? []), 0, 500);
                // Creating new parking space
                $parkingSpace = ParkingSpace::create([
                    'user_id' => Auth::id(),
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                    'address' => $validated['address'],
                    'description' => $validated['description'] ?? '',
                    'capacity' => $validated['capacity'],
                    'contact_info' => $validated['contact_info'],
                    'amenities' => $validated['amenities'] ?? '',
                    'pre_approval_required' => $validated['pre_approval_required'] ?? false,
                    'cancellation_policy' => $validated['cancellation_policy'] ?? '',
                    'access_hours' => $accessHours,  // Use the truncated access_hours value
                    'things_to_know' => $validated['things_to_know'] ?? '',
                    'how_to_redeem' => $validated['how_to_redeem'] ?? '',
                    'close_by_airport' => $validated['close_by_airport'],
                    'rating' => 0,
                ]);
            }
            // Assuming $validated['typeIds'] contains the new type IDs from the user input
            $validated['types'] = $validated['typeIds'];
            unset($validated['typeIds']);

            // Retrieve existing type IDs associated with the parking space
            $existingTypeIds = $parkingSpace->types()->pluck('parking_types.id')->toArray();

            // Determine types to delete (existing types not in new types)
            $typesToDelete = array_diff($existingTypeIds, $validated['types']);

            // Delete types that are no longer selected by the user
            $parkingSpace->types()->detach($typesToDelete);

            // Attach new types to the parking space
            foreach ($validated['types'] as $typeId) {
                // Check if the type already exists before attaching
                if (!in_array($typeId, $existingTypeIds)) {
                    ParkingSpaceType::create([
                        'parking_space_id' => $parkingSpace->id,
                        'parking_type_id' => $typeId,
                    ]);
                }
            }



            if (!empty($validated['pictures'])) {
                $totalImages = count($validated['pictures']);
                if ($totalImages > 5) {
                    return response()->json(['error' => 'You can upload a maximum of 5 images'], 422);
                }

                foreach ($validated['pictures'] as $imageBase64) {
                    foreach ($imageBase64 as $base64) {
                        // dd($base64);
                        // Perform any additional validation if necessary
                        if (!is_string($base64)) {
                            return response()->json(['error' => 'Invalid image format'], 422);
                        } else {
                            ParkingSpacePicture::create([
                                'parking_space_id' => $parkingSpace->id,
                                'image_base64' => $base64,
                            ]);
                        }
                    }
                    // ParkingSpacePicture::create([
                    //     'parking_space_id' => $parkingSpace->id,
                    //     'image_base64' => $imageBase64,
                    // ]);
                }
                Helper::recordAuditLog(
                    'Create|Update',
                    'parkingSpace',
                    $request->id,
                    null,
                    ['admin_user_id' => Auth::id()]
                );
            }

            return response()->json($parkingSpace, $request->has('id') ? 200 : 201);
        } catch (ValidationException $e) {
            //dd($e);
            // Return validation errors as JSON
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // dd($e);
            // Return other errors as JSON
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function updatePricing(Request $request)
    {
        // Validate the request data
        $request->validate([
            'prices' => 'required|array',
            'prices.*.parking_space_id' => 'required|integer',
            'prices.*.type_id' => 'required|numeric', // Adjust validation rules as per your actual data types
            'prices.*.price' => 'required|numeric',
        ]);

        try {
            // Process each price entry and insert/update into the pricing table
            foreach ($request->prices as $priceData) {
                ParkingSpacePrice::updateOrCreate(
                    [
                        'parking_space_id' => $priceData['parking_space_id'],
                        'parking_type_id' => $priceData['type_id'],
                    ],
                    [
                        'price' => $priceData['price'],
                    ]
                );

                Helper::recordAuditLog(
                    'Create|Update',
                    'Pricing',
                    $priceData['parking_space_id'],
                    null,
                    ['admin_user_id' => Auth::id()]
                );
            }

            // Return the request data in the response
            return response()->json(['message' => 'Pricing updated successfully', 'request_data' => $request->all()], 200);
        } catch (\Exception $e) {
            // Handle any exceptions or errors
            return response()->json(['message' => 'Failed to update pricing', 'error' => $e->getMessage()], 500);
        }
    }

    public function findHourlyParking(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'numeric', 'max:255'],
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        // Additional custom validations
        $start_time = Carbon::parse($validated['start_time']);
        $end_time = Carbon::parse($validated['end_time']);
        $now = Carbon::now('Africa/Johannesburg');
        //dd($now);
        // Validate that start_time and end_time are not in the past
        if ($start_time->lessThan($now)) {
            return response()->json(['message' => 'Start time cannot be in the past.'], 422);
        }
        if ($end_time->lessThan($now)) {
            return response()->json(['message' => 'End time cannot be in the past.'], 422);
        }
        // Custom validation to ensure the difference is in full hours or full minutes
        $diffInSeconds = $start_time->diffInSeconds($end_time);

        // Check if the difference is not a multiple of 60 seconds (1 minute)
        if ($diffInSeconds % 60 !== 0) {
            return response()->json(['message' => 'The time difference between start and end time must be in full hours or full minutes.'], 422);
        }
        try {
            $type = $validated['type'];
            $latitude = $validated['latitude'];
            $longitude = $validated['longitude'];

            // Calculate duration in minutes
            $durationMinutes = $start_time->diffInMinutes($end_time);

            // Round up to the nearest hour
            $durationHours = ceil($durationMinutes / 60);

            $radius = 50;
            $boundingBox = $this->calculateBoundingBox($latitude, $longitude, $radius);

            $nearbyParkingSpaces = ParkingSpace::with([
                'pictures',
                'prices' => function ($query) {
                    $query->where('parking_type_id', 1); // Ensure the price is for the correct parking type
                },
                'types' => function ($query) use ($type) {
                    $query->where('parking_type_id', $type);
                },
                'reviews' => function ($query) {
                    $query->with('aspectRatings'); // Include aspect ratings for reviews
                }
            ])
                ->selectRaw("
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
                    $query->where('parking_space_types.parking_type_id', $type);
                })
                ->whereHas('prices', function ($query) {
                    $query->where('parking_type_id', 1);
                })
                ->whereDoesntHave('reservations', function ($query) use ($start_time, $end_time) {
                    $query->where(function ($query) use ($start_time, $end_time) {
                        $query->where('start_time', '<', $end_time)
                            ->where('end_time', '>', $start_time);
                    });
                })
                ->where('availability', 1) // Only select available parking spaces
                ->get();

            // Modify the price based on the duration and update the existing price in the array
            $nearbyParkingSpaces->each(function ($space) use ($durationHours) {
                $priceRecord = $space->prices->first(); // Get the first price record related to the parking type
                if ($priceRecord) {
                    $priceRecord->price = $priceRecord->price * $durationHours; // Update the price directly
                }
            });

            return response()->json($nearbyParkingSpaces);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Server error', 'error' => $e->getMessage()], 500);
        }
    }



    // public function findHourlyParking(Request $request)
    // {
    //     $validated = $request->validate([
    //         'type' => ['required', 'numeric', 'max:255'],
    //         'latitude' => 'required|numeric',
    //         'longitude' => 'required|numeric',
    //         'start_time' => 'required|string',
    //         'end_time' => 'required|string|after:start_time',
    //     ]);
    //     dd($validated);
    //     try {
    //         $type = $validated['type'];
    //         $latitude = $validated['latitude'];
    //         $longitude = $validated['longitude'];
    //         $start_time = Carbon::parse($validated['start_time']); // Parse ISO 8601 datetime
    //         $end_time = Carbon::parse($validated['end_time']);     // Parse ISO 8601 datetime

    //         $radius = 50;
    //         $boundingBox = $this->calculateBoundingBox($latitude, $longitude, $radius);

    //         $nearbyParkingSpaces = ParkingSpace::with([
    //             'pictures',
    //             'prices' => function ($query) {
    //                 $query->where('parking_type_id', 1);
    //             },
    //             'types' => function ($query) use ($type) {
    //                 $query->where('parking_type_id', 1);
    //             },
    //             'reviews' => function ($query) {
    //                 $query->with('aspectRatings'); // Include aspect ratings for reviews
    //             }
    //         ])
    //             ->selectRaw("
    //                 *,
    //                 (6371 * acos(
    //                     cos(radians($latitude)) *
    //                     cos(radians(latitude)) *
    //                     cos(radians(longitude) - radians($longitude)) +
    //                     sin(radians($latitude)) *
    //                     sin(radians(latitude))
    //                 )) AS distance")
    //             ->having('distance', '<', $radius)
    //             ->whereBetween('latitude', [$boundingBox['min_lat'], $boundingBox['max_lat']])
    //             ->whereBetween('longitude', [$boundingBox['min_lng'], $boundingBox['max_lng']])
    //             ->whereHas('types', function ($query) use ($type) {
    //                 $query->where('parking_space_types.parking_type_id', $type);
    //             })
    //             ->whereHas('prices', function ($query) {
    //                 $query->where('parking_type_id', 1);
    //             })
    //             ->whereDoesntHave('reservations', function ($query) use ($start_time, $end_time) {
    //                 $query->where(function ($query) use ($start_time, $end_time) {
    //                     $query->where('start_time', '<', $end_time)
    //                         ->where('end_time', '>', $start_time);
    //                 });
    //             })
    //             ->get();

    //         return response()->json($nearbyParkingSpaces);
    //     } catch (ValidationException $e) {
    //         return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
    //     } catch (\Exception $e) {
    //         return response()->json(['message' => 'Server error', 'error' => $e->getMessage()], 500);
    //     }
    // }



    public function findAirportParking(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'airport_name' => 'required|string|max:255',
            'start_time' => 'required|date', // Changed from 'string' to 'date' for better validation
            'end_time' => 'required|date|after:start_time',
        ]);

        try {
            // Retrieve the necessary data from the validated input
            $airportName = $validated['airport_name'];
            $start_time = Carbon::parse($validated['start_time']);
            $end_time = Carbon::parse($validated['end_time']);
            $now = Carbon::now('Africa/Johannesburg');
            // dd($start_time);
            // dd($now);
            // Validate that start_time and end_time are not in the past
            if ($start_time->lessThan($now)) {
                return response()->json(['message' => 'Start time cannot be in the past.'], 422);
            }
            if ($end_time->lessThan($now)) {
                return response()->json(['message' => 'End time cannot be in the past.'], 422);
            }

            // Custom validation to ensure the difference is in full hours or full minutes
            $diffInSeconds = $start_time->diffInSeconds($end_time);

            // Check if the difference is not a multiple of 60 seconds (1 minute)
            if ($diffInSeconds % 60 !== 0) {
                return response()->json(['message' => 'The time difference between start and end time must be in full hours or full minutes.'], 422);
            }

            // Calculate duration in minutes
            $durationMinutes = $start_time->diffInMinutes($end_time);

            // Round up to the nearest hour
            $durationHours = ceil($durationMinutes / 60);

            // Define the radius (in kilometers) for the search, if needed
            $radius = 50;

            // Query to find parking spaces near the specified airport with required conditions
            $nearbyParkingSpaces = ParkingSpace::with([
                'pictures',
                'prices' => function ($query) {
                    $query->where('parking_type_id', 3); // Filter pricing for airport type (ID 3)
                },
                'types'
            ])
                ->where('close_by_airport', 'LIKE', "%{$airportName}%")
                ->whereHas('prices', function ($query) {
                    $query->where('parking_type_id', 3); // Ensure there's a price for airport type (ID 3)
                })
                ->whereDoesntHave('reservations', function ($query) use ($start_time, $end_time) {
                    $query->where(function ($query) use ($start_time, $end_time) {
                        $query->where('start_time', '<', $end_time)
                            ->where('end_time', '>', $start_time);
                    });
                })
                ->where('availability', 1) // Only select available parking spaces
                ->get();

            // Modify the price based on the duration and update the existing price in the array
            $nearbyParkingSpaces->each(function ($space) use ($durationHours) {
                $priceRecord = $space->prices->first(); // Get the first price record related to the parking type
                if ($priceRecord) {
                    $priceRecord->price = $priceRecord->price * $durationHours; // Update the price directly
                }
            });

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

    public function toggleAvailability($id)
    {
        $parkingSpace = ParkingSpace::find($id);

        if (!$parkingSpace) {
            return response()->json(['error' => 'Parking space not found'], 404);
        }

        // Toggle the availability
        $parkingSpace->availability = !$parkingSpace->availability;
        $parkingSpace->save();

        return response()->json([
            'message' => 'Availability updated successfully',
            'parking_space' => $parkingSpace,
        ]);
    }


    public function checkAvailability(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'parking_space_id' => 'required|integer',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        // Check if the parking space exists
        $parkingSpace = ParkingSpace::find($request->parking_space_id);
        if (!$parkingSpace) {
            return response()->json(['message' => 'Parking space not found'], 404);
        }

        // Check for conflicting reservations
        $conflictingReservations = Reservation::where('parking_space_id', $request->parking_space_id)
            ->where(function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    // Check if the requested start time is before an existing reservation's end time
                    // and if the requested end time is after an existing reservation's start time
                    $query->where('start_time', '<', $request->end_time)
                        ->where('end_time', '>', $request->start_time);
                });
            })
            ->exists();

        if ($conflictingReservations) {
            return response()->json(['available' => false, 'message' => 'Parking space is no longer available for the requested time range'], 200);
        }

        return response()->json(['available' => true, 'message' => 'Parking space is available'], 200);
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



    public function getParkingSpace($id, Request $request)
    {
        //dd($id);
        // Validate the incoming request
        $validated = $request->validate([
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'type' => 'required|string|in:Hourly,Airport',
        ]);
        //dd($request);
        $start_time = Carbon::parse($validated['start_time']);
        $end_time = Carbon::parse($validated['end_time']);
        $type = $validated['type'];
        $now = Carbon::now('Africa/Johannesburg');

        // Validate that start_time and end_time are not in the past
        if ($start_time->lessThan($now)) {
            return response()->json(['message' => 'Start time cannot be in the past.'], 422);
        }
        if ($end_time->lessThan($now)) {
            return response()->json(['message' => 'End time cannot be in the past.'], 422);
        }

        // Custom validation to ensure the difference is in full hours or full minutes
        $diffInSeconds = $start_time->diffInSeconds($end_time);
        if ($diffInSeconds % 60 !== 0) {
            return response()->json(['message' => 'The time difference between start and end time must be in full hours or full minutes.'], 422);
        }

        // Check if the parking space exists
        $parkingSpace = ParkingSpace::find($id);
        if (!$parkingSpace) {
            return response()->json(['message' => 'Parking space not found'], 404);
        }
        // Check if the parking space is available
        if (!$parkingSpace->available) {
            return response()->json(['message' => 'This parking space is not available'], 422);
        }
        // Load related data
        $parkingSpace = ParkingSpace::with([
            'types',
            'pictures',
            'reservations',
            'reviews',
            'prices' => function ($query) use ($type) {
                $parkingTypeId = $type === 'Hourly' ? 1 : 3; // Adjust parking_type_id based on type
                $query->where('parking_type_id', $parkingTypeId);
            },
        ])->findOrFail($id);
        //dd($parkingSpace);
        // Modify the price based on the duration and type
        $priceRecord = $parkingSpace->prices->first(); // Get the first price record related to the parking type
        if ($priceRecord) {
            $durationMinutes = $start_time->diffInMinutes($end_time);
            $durationHours = ceil($durationMinutes / 60);
            $priceRecord->price = $priceRecord->price * $durationHours;
        }

        return response()->json($parkingSpace);
    }



    // Update a specific parking space
    public function updateParkingSpace(Request $request, $id)
    {
        dd($request);
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

        // Delete related pictures
        foreach ($parkingSpace->pictures as $picture) {
            $picture->delete();
        }

        // Delete related types (pivot table records if using many-to-many)
        $parkingSpace->types()->detach();

        // Finally, delete the parking space itself
        $parkingSpace->delete();

        return response()->json(['message' => 'Parking space deleted successfully']);
    }


    // List available parking types
    public function listParkingTypes()
    {
        $parkingTypes = ParkingType::all();
        return response()->json($parkingTypes);
    }

    // public function getMyParkingSpaces()
    // {
    //     $parkingSpaces = ParkingSpace::where('user_id', Auth::id())->with(['types', 'pictures', 'reservations', 'prices'])->get();
    //     //.($parkingSpaces);
    //     return response()->json($parkingSpaces);
    // }
    public function getMyParkingSpaces()
    {
        $userId = Auth::id();

        // Fetch parking spaces where the user is the owner
        $ownedParkingSpaces = ParkingSpace::where('user_id', $userId)->with(['types', 'pictures', 'reservations', 'prices'])->get();

        // Fetch parking spaces where the user is an assigned admin
        $adminParkingSpaces = ParkingSpace::whereHas('admins', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with(['types', 'pictures', 'reservations', 'prices'])->get();

        // Merge the collections
        $allParkingSpaces = $ownedParkingSpaces->merge($adminParkingSpaces);

        return response()->json($allParkingSpaces);
    }

    public function addAdmin(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            // Find the user by email
            $user = User::where('email', $request->email)->firstOrFail();

            // Find all parking spaces for the authenticated user
            $parkingSpaces = ParkingSpace::where('user_id', Auth::id())->get();

            // Check if there are no parking spaces for the user
            if ($parkingSpaces->isEmpty()) {
                return response()->json(['message' => 'No parking spaces found for the user'], 404);
            }

            // Attach the user as an admin to each parking space
            foreach ($parkingSpaces as $parkingSpace) {
                // Check if the user is already an admin for the parking space
                if (!$parkingSpace->admins->contains($user->id)) {
                    $parkingSpace->admins()->attach($user);
                }
            }

            return response()->json(['message' => 'Admin assigned to all parking spaces successfully'], 200);
        } catch (ValidationException $e) {
            // Handle the case where the user with the specified email is not found
            return response()->json(['message' => 'User not found'], 404);
        } catch (\Exception $e) {
            // Handle other exceptions and return an appropriate response
            return response()->json(['message' => 'Server error', 'error' => $e->getMessage()], 500);
        }
    }
}
