<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewAspect extends Model
{
    protected $fillable = ['name'];

    public function ratings()
    {
        return $this->hasMany(ReviewAspectRating::class);
    }
}
