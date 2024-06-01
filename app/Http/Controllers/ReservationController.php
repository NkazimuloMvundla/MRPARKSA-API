<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException; // Import ValidationException

class ReservationController extends Controller
{
    // List reservations of the authenticated user
    public function listUserReservations()
    {
        $userId = Auth::id();
        $reservations = Reservation::where('user_id', $userId)->with('parkingSpace')->get();
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
        try {
            // Validate the incoming request
            $validated = $request->validate([
                'parking_space_id' => 'required|exists:parking_spaces,id',
                'parking_type_id' => 'required|exists:parking_types,id', // hourly, monthly, airport
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time',
                'price' => 'required|numeric', // amount paid
            ]);

            // Create the reservation
            $reservation = Reservation::create([
                'user_id' => Auth::id(),
                'parking_space_id' => $validated['parking_space_id'],
                'parking_type_id' => $validated['parking_type_id'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'price' => $validated['price'],
            ]);

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


}
