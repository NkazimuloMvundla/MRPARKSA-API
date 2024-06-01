<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['user_id', 'parking_space_id', 'comment', 'star_rating'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parkingSpace()
    {
        return $this->belongsTo(ParkingSpace::class);
    }

    public function aspectRatings()
    {
        return $this->hasMany(ReviewAspectRating::class);
    }
}
