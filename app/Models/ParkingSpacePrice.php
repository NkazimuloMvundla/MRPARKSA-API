<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingSpacePrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'parking_space_id',
        'parking_type_id',
        'price',
    ];

    public function parkingSpace()
    {
        return $this->belongsTo(ParkingSpace::class);
    }

    public function parkingType()
    {
        return $this->belongsTo(ParkingType::class);
    }
}
