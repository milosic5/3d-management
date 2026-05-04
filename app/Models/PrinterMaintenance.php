<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrinterMaintenance extends Model
{
    protected $fillable = [
        'printer_id',
        'maintenance_month',
        'working_hours_at_maintenance',
        'hours_printed_this_month',
        'lubricated',
    ];

    protected $casts = [
        'maintenance_month' => 'date',
        'lubricated' => 'boolean',
    ];

    public function printer()
    {
        return $this->belongsTo(Printer::class);
    }
}
