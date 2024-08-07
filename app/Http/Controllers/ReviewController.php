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

    public function listUserReviews($parking_space_id)
    {
        $userId = Auth::id();
        $reviews = Review::where('user_id', $userId)
            ->where('parking_space_id', $parking_space_id)
            ->with(['parkingSpace', 'aspectRatings']) // Make sure to include aspectRatings
            ->get();
        return response()->json($reviews);
    }


    public function submitReview(Request $request)
    {
        // Use a fake ID for user since Auth is not configured
        $userId = Auth::id(); // Get the authenticated user's ID

        try {
            // Validate the request
            $validated = $request->validate([
                'parking_space_id' => 'required|exists:parking_spaces,id',
                'comment' => 'required|string',
                'star_rating' => 'required|integer|between:1,5',
                'aspect_ratings' => 'required|array',
                'aspect_ratings.*.review_aspect_id' => 'required|integer|in:1,2,3', // Validate review_aspect_id
                'aspect_ratings.*.percentage' => 'required|integer|between:1,5',
            ]);

            // Check if the user already has a review for this parking space
            $review = Review::where('user_id', $userId)
                ->where('parking_space_id', $validated['parking_space_id'])
                ->first();

            if ($review) {
                // Update the existing review
                $review->update([
                    'comment' => $validated['comment'],
                    'star_rating' => $validated['star_rating'],
                ]);

                // Delete existing aspect ratings for this review
                ReviewAspectRating::where('review_id', $review->id)->delete();
            } else {
                // Create a new review
                $review = Review::create([
                    'user_id' => $userId, // Use the authenticated user's ID
                    'parking_space_id' => $validated['parking_space_id'],
                    'comment' => $validated['comment'],
                    'star_rating' => $validated['star_rating'],
                ]);
            }

            // Get the aspect ratings
            $aspectRatings = $validated['aspect_ratings'];

            // Create the aspect ratings
            foreach ($aspectRatings as $aspectRating) {
                // $aspectRating is an associative array with 'review_aspect_id' and 'percentage'
                $reviewAspectId = $aspectRating['review_aspect_id'];
                $percentage = $aspectRating['percentage'];

                ReviewAspectRating::create([
                    'review_id' => $review->id,
                    'review_aspect_id' => $reviewAspectId,
                    'percentage' => $percentage
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
