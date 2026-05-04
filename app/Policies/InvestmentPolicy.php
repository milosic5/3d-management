<?php

namespace App\Policies;

use App\Models\Investment;
use App\Models\User;

class InvestmentPolicy
{
    public function viewAny(User $user): bool { return false; }
    public function view(User $user, Investment $investment): bool { return false; }
    public function create(User $user): bool { return false; }
    public function update(User $user, Investment $investment): bool { return false; }
    public function delete(User $user, Investment $investment): bool { return false; }
    public function restore(User $user, Investment $investment): bool { return false; }
    public function forceDelete(User $user, Investment $investment): bool { return false; }
}
