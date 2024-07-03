<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParkingSpacePicture extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'parking_space_id', 'image_base64',
    ];

    public function parkingSpace()
    {
        return $this->belongsTo(ParkingSpace::class);
    }
}
