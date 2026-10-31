<?php

namespace App\Policies;

use App\Models\Packaging;
use App\Models\User;

/**
 * Operators may manage packaging (incl. stock changes) but not delete it.
 * Admins pass every check through Gate::before.
 */
class PackagingPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Packaging $packaging): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Packaging $packaging): bool
    {
        return true;
    }

    public function delete(User $user, ?Packaging $packaging = null): bool
    {
        return false;
    }

    public function restore(User $user, ?Packaging $packaging = null): bool
    {
        return false;
    }

    public function forceDelete(User $user, ?Packaging $packaging = null): bool
    {
        return false;
    }
}
