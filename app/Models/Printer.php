<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Printer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'total_working_hours',
        'last_nozzle_change_date',
        'last_nozzle_working_hours',
        'current_nozzle_diameter',
    ];

    protected $casts = [
        'last_nozzle_change_date' => 'datetime',
        'total_working_hours' => 'integer',
        'last_nozzle_working_hours' => 'integer',
    ];

    public function getCurrentNozzleHoursAttribute()
    {
        return max(0, $this->total_working_hours - $this->last_nozzle_working_hours);
    }

    public function getNeedsMaintenanceAttribute()
    {
        $now = now();
        if ($now->day >= 28) {
            $targetMonth = $now->copy()->addMonth()->startOfMonth();
        } else {
            $targetMonth = $now->copy()->startOfMonth();
        }

        // Avoid a query per printer when the maintenances were eager loaded.
        if ($this->relationLoaded('maintenances')) {
            return ! $this->maintenances->contains(
                fn (PrinterMaintenance $maintenance) => $maintenance->maintenance_month->isSameDay($targetMonth)
            );
        }

        return ! $this->maintenances()->whereDate('maintenance_month', $targetMonth->format('Y-m-d'))->exists();
    }

    public function nozzleChanges()
    {
        return $this->hasMany(PrinterNozzleChange::class);
    }

    public function maintenances()
    {
        return $this->hasMany(PrinterMaintenance::class);
    }
}
