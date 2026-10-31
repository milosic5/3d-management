<?php

namespace App\Policies;

use App\Models\Calibration;
use App\Models\User;

/**
 * Operators may manage calibrations but not delete them.
 * Admins pass every check through Gate::before.
 */
class CalibrationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Calibration $calibration): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Calibration $calibration): bool
    {
        return true;
    }

    public function delete(User $user, ?Calibration $calibration = null): bool
    {
        return false;
    }

    public function restore(User $user, ?Calibration $calibration = null): bool
    {
        return false;
    }

    public function forceDelete(User $user, ?Calibration $calibration = null): bool
    {
        return false;
    }
}
