<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrinterNozzleChange extends Model
{
    protected $fillable = [
        'printer_id',
        'nozzle_diameter',
        'working_hours_at_change',
    ];

    public function printer()
    {
        return $this->belongsTo(Printer::class);
    }
}
