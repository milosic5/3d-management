<?php

namespace App\Policies;

use App\Models\Printer;
use App\Models\User;

/**
 * Operators may manage printers (incl. maintenance and nozzle resets) but not delete anything.
 * Admins pass every check through Gate::before.
 */
class PrinterPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Printer $printer): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Printer $printer): bool
    {
        return true;
    }

    public function delete(User $user, ?Printer $printer = null): bool
    {
        return false;
    }

    public function deleteMaintenance(User $user, ?Printer $printer = null): bool
    {
        return false;
    }

    public function restore(User $user, ?Printer $printer = null): bool
    {
        return false;
    }

    public function forceDelete(User $user, ?Printer $printer = null): bool
    {
        return false;
    }
}
