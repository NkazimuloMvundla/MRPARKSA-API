<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingType extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function parkingSpaces()
    {
        return $this->belongsToMany(ParkingSpace::class, 'parking_space_types');
    }
    public function prices()
    {
        return $this->hasMany(ParkingSpacePrice::class);
    }
}
