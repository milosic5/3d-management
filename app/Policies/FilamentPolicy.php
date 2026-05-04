<?php

namespace App\Policies;

use App\Models\Filament;
use App\Models\User;

class FilamentPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Filament $filament): bool { return true; }
    public function create(User $user): bool { return true; }
    public function update(User $user, Filament $filament): bool { return true; }
    public function delete(User $user, Filament $filament): bool { return false; }
    public function restore(User $user, Filament $filament): bool { return false; }
    public function forceDelete(User $user, Filament $filament): bool { return false; }
}
