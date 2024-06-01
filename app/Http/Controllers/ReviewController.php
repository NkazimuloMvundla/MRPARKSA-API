<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ParkingSpace;
use Illuminate\Support\Facades\Auth;
use App\Models\ParkingSpacePicture;
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
class ReviewController extends Controller
{
    // List reviews for a specific parking space
    public function listReviews($id)
    {
        $reviews = Review::where('parking_space_id', $id)->with('user')->get();
        return response()->json($reviews);
    }

    // List reviews submitted by the authenticated user
    public function listUserReviews()
    {
        $userId = Auth::id();
        $reviews = Review::where('user_id', $userId)->with('parkingSpace')->get();
        return response()->json($reviews);
    }

    public function submitReview(Request $request)
    {
      //  dd($request);
      $users = User::all();
        try {
            $validated = $request->validate([
                // 'user_id' => 'required|exists:parking_spaces,id',
                'parking_space_id' => 'required|exists:parking_spaces,id',
                'comment' => 'required|string',
                'star_rating' => 'required|integer|between:1,5',
                'aspect_ratings' => 'required|array',
                'aspect_ratings.safety' => 'required|integer|between:1,5',
                'aspect_ratings.Ease of Finding' => 'required|integer|between:1,5',
                'aspect_ratings.size' => 'required|integer|between:1,5',
            ]);

            // Create the review
            $review = Review::create([
                'user_id' => User::first()->id, // we not auth so this wont work. you must add the middleware for this to work, for now use FAKE ID 5
                'parking_space_id' => $validated['parking_space_id'],
                'comment' => $validated['comment'],
                'star_rating' => $validated['star_rating'],
            ]);

            // Get the aspect ratings
            $aspectRatings = $validated['aspect_ratings'];
            // Create the aspect ratings
            foreach ($aspectRatings as $aspect => $rating) {
                $aspectName = ucfirst($aspect);
                $aspectRecord = ReviewAspect::where('name', $aspectName)->first();

                if (!$aspectRecord) {
                    return response()->json([
                        'message' => "Aspect '{$aspectName}' not found."
                    ], 400); // 400 Bad Request
                }

              ReviewAspectRating::create([
                    'review_id' => $review->id,
                    'review_aspect_id' => $aspectRecord->id,
                    'percentage' => $rating * 20, // Convert star rating (1-5) to percentage (0-100)
              ]);
            }

            return response()->json(['message' => 'Review submitted successfully'], 201);

        } catch (ValidationException $e) {
            // Handle validation exceptions
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }
}
