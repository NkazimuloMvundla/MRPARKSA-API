<?php

namespace App\Policies;

use App\Models\ParkingSpace;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class ParkingSpacePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any parking spaces.
     */
    public function viewAny(User $user)
    {
        return true; // Example: Allow all users to view any parking spaces
    }

    /**
     * Determine whether the user can view the parking space.
     */
    public function view(User $user, ParkingSpace $parkingSpace)
    {
        return true; // Example: Allow all users to view a specific parking space
    }

    /**
     * Determine whether the user can create parking spaces.
     */
    public function create(User $user)
    {
        return $user->isAdmin(); // Example: Only allow admins to create parking spaces
    }

    /**
     * Determine whether the user can update the parking space.
     */
    public function update(User $user, ParkingSpace $parkingSpace)
    {
        return $user->id === $parkingSpace->user_id; // Example: Only allow the owner to update the parking space
    }

    /**
     * Determine whether the user can delete the parking space.
     */
    public function delete(User $user, ParkingSpace $parkingSpace)
    {
        return $user->id === $parkingSpace->user_id; // Example: Only allow the owner to delete the parking space
    }
}
