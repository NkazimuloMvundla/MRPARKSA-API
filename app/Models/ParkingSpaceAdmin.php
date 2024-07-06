<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingSpaceAdmin extends Model
{
    use HasFactory;

    protected $table = 'parking_space_admins';

    protected $fillable = [
        'parking_space_id',
        'user_id',
    ];

    /**
     * Get the parking space that the admin is associated with.
     */
    public function parkingSpace()
    {
        return $this->belongsTo(ParkingSpace::class);
    }

    /**
     * Get the user that is an admin of the parking space.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
