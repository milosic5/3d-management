<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Calibration extends Model
{
    protected $fillable = [
        'filament_id',
        'temperature',
        'flow_ratio',
        'pressure_advance',
        'max_volumetric_speed'
    ];

    public function filament(): BelongsTo
    {
        return $this->belongsTo(Filament::class);
    }
}
