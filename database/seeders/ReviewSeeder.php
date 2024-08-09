<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\ParkingSpace;
use App\Models\User;
use App\Models\ReviewAspect;
use App\Models\ReviewAspectRating;

class ReviewSeeder extends Seeder
{
    public function run()
    {
        // Fetch random parking spaces and users
        $parkingSpaces = ParkingSpace::all();
        $users = User::all();

        // Define some example reviews
        $reviews = [
            [
                'comment' => 'Great parking space!',
                'star_rating' => 5,
                'aspect_ratings' => [
                    'safety' => 5,
                    'ease_of_finding' => 4,
                    'size' => 5,
                ]
            ],
            [
                'comment' => 'Good location but a bit tight.',
                'star_rating' => 4,
                'aspect_ratings' => [
                    'safety' => 4,
                    'ease_of_finding' => 3,
                    'size' => 2,
                ]
            ],
            // Add more reviews as needed
        ];

        foreach ($reviews as $reviewData) {
            // Pick random parking space and user for the review
            $parkingSpace = $parkingSpaces->random();
            $user = $users->random();

            // Create the review
            $review = Review::create([
                'user_id' => 2,
                'parking_space_id' => 1,
                'comment' => $reviewData['comment'],
                'star_rating' => $reviewData['star_rating'],
            ]);

            // // Create the aspect ratings
            foreach ($reviewData['aspect_ratings'] as $aspectName => $rating) {
                $aspect = ReviewAspect::where('name', ucfirst($aspectName))->first();
                ReviewAspectRating::create([
                    'review_id' => $review->id,
                    'review_aspect_id' => $aspect->id,
                    'percentage' => 3, // Convert star rating to percentage
                ]);
            }
        }
    }
}
