<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewAspectRating extends Model
{
    protected $fillable = ['review_id', 'review_aspect_id', 'percentage'];

    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    public function aspect()
    {
        return $this->belongsTo(ReviewAspect::class, 'review_aspect_id');
    }
}
