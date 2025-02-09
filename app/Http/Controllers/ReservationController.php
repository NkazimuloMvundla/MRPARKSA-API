<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException; // Import ValidationException
use App\Models\ParkingSpace;
use App\Http\helpers\Helper;
use Carbon\Carbon;
use App\Mail\ReservationCreated;
use Illuminate\Support\Facades\Mail;

class ReservationController extends Controller
{
    // List reservations made by the authenticated user
    public function listUserReservations()
    {
        $userId = Auth::id();
        $reservations = Reservation::where('user_id', $userId)
            ->with(['parkingSpace', 'parkingType'])
            ->orderBy('created_at', 'desc')  // Order by creation date, descending
            ->get();

        return response()->json($reservations);
    }


    // List reservations for parking spaces owned by the authenticated user
    public function listOwnerReservations()
    {
        $userId = Auth::id();

        // Fetch IDs of parking spaces owned by the user
        $ownerParkingSpaces = ParkingSpace::where('user_id', $userId)->pluck('id');

        // Retrieve reservations linked to these parking spaces
        $reservations = Reservation::whereIn('parking_space_id', $ownerParkingSpaces)
            ->with(['user', 'parkingType'])
            ->orderBy('created_at', 'desc')  // Order by creation date, descending
            ->get();

        return response()->json($reservations);
    }

    // Cancel a reservation
    public function cancelReservation($id)
    {
        $reservation = Reservation::where('user_id', Auth::id())->findOrFail($id);
        $reservation->delete(); //dont delete, just mark as deleted
        return response()->json(['message' => 'Reservation canceled successfully']);
    }


    public function makeReservation(Request $request)
    {
        $confirmationNumber = Helper::generateConfirmationNumber();

        try {
            // Validate the incoming request
            $validated = $request->validate([
                'parking_space_id' => 'required|exists:parking_spaces,id',
                'parking_type_id' => 'required|exists:parking_types,id', // hourly, monthly, airport
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time',
                'price' => 'required|numeric', // amount paid,
                'vehicle_license_number' => 'required|string',
                'vehicle_size' => 'required|string',
            ]);

            $start_time = Carbon::parse($validated['start_time']); // Parse ISO 8601 datetime
            $end_time = Carbon::parse($validated['end_time']);     // Parse ISO 8601 datetime

            $parkingSpace = ParkingSpace::findOrFail($validated['parking_space_id']);
            $status = $parkingSpace->pre_approval_required ? 'pending' : 'approved';

            // Check for conflicting reservations
            $conflictingReservations = Reservation::where('parking_space_id', $validated['parking_space_id'])
                ->where(function ($query) use ($start_time, $end_time) {
                    $query->whereBetween('start_time', [$start_time, $end_time])
                        ->orWhereBetween('end_time', [$start_time, $end_time])
                        ->orWhere(function ($query) use ($start_time, $end_time) {
                            $query->where('start_time', '<=', $start_time)
                                ->where('end_time', '>=', $end_time);
                        });
                })
                ->exists();

            if ($conflictingReservations) {
                return response()->json(['message' => 'Parking space is no longer available for the requested time range'], 422);
            }

            // Create the reservation
            $reservation = Reservation::create([
                'user_id' => Auth::id(),
                'parking_space_id' => $validated['parking_space_id'],
                'parking_type_id' => $validated['parking_type_id'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'price' => $validated['price'],
                'status' => $status,
                'vehicle_license_number' => $validated['vehicle_license_number'],
                'vehicle_size' => $validated['vehicle_size'],
                'confirmation_number' => $confirmationNumber,
            ]);
            // Retrieve the parking space address
            $address = $parkingSpace->address;
            try {
                Mail::to(Auth::user()->email)->send(new ReservationCreated($reservation,$address));
            } catch (\Exception $e) {
                // Log::error('Failed to send reservation confirmation email: ' . $e->getMessage());
                return response()->json(['message' => 'Failed to send reservation confirmation email', 'errors' => $$e->getMessage()], 201);
                // You can also return a response or handle the error in another way
            }
            // Return a successful response
            return response()->json(['message' => 'Reservation created successfully', 'reservation' => $reservation], 201);
        } catch (ValidationException $e) {
            // Handle validation exceptions
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }
    // Get details of a specific reservation
    public function getReservationDetails($id)
    {
        $reservation = Reservation::with('parkingSpace')->findOrFail($id);
        return response()->json($reservation);
    }

    public function approveReservation($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->status = 'approved';
        $reservation->save();

        // Process payment if necessary
        // ...

        return response()->json(['message' => 'Reservation approved successfully', 'reservation' => $reservation], 200);
    }

    public function rejectReservation($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->status = 'rejected';
        $reservation->save();

        return response()->json(['message' => 'Reservation rejected successfully', 'reservation' => $reservation], 200);
    }

    //     public function createReservation(Request $request)
    // {
    //     $validatedData = $request->validate([
    //         'parking_space_id' => 'required|exists:parking_spaces,id',
    //         'parking_type_id' => 'required|exists:parking_types,id',
    //         'start_time' => 'required|date',
    //         'end_time' => 'required|string',
    //     ]);

    //     $parkingType = ParkingType::find($validatedData['parking_type_id']);

    //     DB::beginTransaction();
    //     try {
    //         $reservation = Reservation::create([
    //             'user_id' => Auth::id(),
    //             'parking_space_id' => $validatedData['parking_space_id'],
    //             'parking_type_id' => $validatedData['parking_type_id'],
    //             'start_time' => $validatedData['start_time'],
    //             'end_time' => $validatedData['end_time'],
    //             'price' => null,
    //             'status' => 'Pending',
    //             'pre_approval_required' => $parkingType->pre_approval_required,
    //         ]);

    //         // Placeholder for payment authorization logic
    //         $paymentAuthorized = true;

    //         if ($parkingType->pre_approval_required && $paymentAuthorized) {
    //             // Notify the parking space owner for approval
    //             Notification::send($reservation->parkingSpace->owner, new ReservationApprovalRequest($reservation));
    //         } elseif (!$parkingType->pre_approval_required && $paymentAuthorized) {
    //             $reservation->update(['price' => $this->calculatePrice($validatedData), 'status' => 'Confirmed']);
    //         }

    //         DB::commit();
    //         return response()->json($reservation, 201);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['error' => 'Failed to create reservation'], 500);
    //     }
    // }

    // public function approveReservation(Request $request, Reservation $reservation)
    // {
    //     $validatedData = $request->validate([
    //         'price' => 'required|numeric',
    //     ]);

    //     DB::beginTransaction();
    //     try {
    //         $reservation->update(['price' => $validatedData['price'], 'status' => 'Confirmed']);

    //         // Placeholder for payment capture logic
    //         $paymentCaptured = true;

    //         if ($paymentCaptured) {
    //             DB::commit();
    //             return response()->json(['message' => 'Reservation approved and payment captured']);
    //         } else {
    //             throw new \Exception('Payment capture failed');
    //         }
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['error' => 'Failed to approve reservation'], 500);
    //     }
    // }
}
