<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingSpacePicture extends Model
{
    use HasFactory;

    protected $fillable = [
        'parking_space_id', 'image_path',
    ];

    public function parkingSpace()
    {
        return $this->belongsTo(ParkingSpace::class);
    }
}
