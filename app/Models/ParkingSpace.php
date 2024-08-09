<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParkingSpace extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'type', 'close_by_airport', 'latitude', 'longitude', 'address', 'description',
        'capacity', 'contact_info', 'amenities', 'rating', 'pre_approval_required',
        'cancellation_policy', 'access_hours', 'things_to_know', 'how_to_redeem'
    ];

    protected $casts = [
        'amenities' => 'array',
        'pre_approval_required' => 'boolean',
    ];

    protected $appends = ['redeem_steps'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pictures()
    {
        return $this->hasMany(ParkingSpacePicture::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
    // Add this relationship
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    public function types()
    {
        return $this->belongsToMany(ParkingType::class, 'parking_space_types');
    }

    // Existing relationships

    public function admins()
    {
        return $this->belongsToMany(User::class, 'parking_space_admins');
    }

    public function parkingSpaceAdmins()
    {
        return $this->hasMany(ParkingSpaceAdmin::class);
    }


    public function prices()
    {
        return $this->hasMany(ParkingSpacePrice::class);
    }

    public function getRedeemStepsAttribute()
    {
        $redeemSteps = json_decode($this->attributes['how_to_redeem'], true);
        return is_array($redeemSteps) ? $redeemSteps : [$this->attributes['how_to_redeem']];
    }
}
