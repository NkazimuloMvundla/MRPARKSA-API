<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingSpace extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'latitude', 'longitude', 'address', 'description', 'price',
        'capacity', 'contact_info', 'amenities', 'rating'
    ];

    protected $casts = [
        'amenities' => 'array',
    ];

    public function pictures()
    {
        return $this->hasMany(ParkingSpacePicture::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function types()
    {
        return $this->belongsToMany(ParkingType::class, 'parking_space_types');
    }
}
