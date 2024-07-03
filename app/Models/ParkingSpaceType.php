<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParkingSpaceType extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['parking_space_id', 'parking_type_id'];

    public function parkingSpace()
    {
        return $this->belongsTo(ParkingSpace::class);
    }

    public function parkingType()
    {
        return $this->belongsTo(ParkingType::class);
    }
}
