<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'parking_space_id',
        'parking_type_id',
        'start_time',
        'end_time',
        'price',
        'status',
        'vehicle_license_number',
        'vehicle_size',
        'confirmation_number'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parkingSpace()
    {
        return $this->belongsTo(ParkingSpace::class);
    }

    public function parkingType()
    {
        return $this->belongsTo(ParkingType::class);
    }
}
