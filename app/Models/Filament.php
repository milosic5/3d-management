<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Filament extends Model
{
    protected $fillable = [
        'brand', 'name', 'material', 'color_name',
        'color_hex', 'price_per_kg', 'empty_spool_weight_grams', 'notes'
    ];

    protected function casts(): array
    {
        return [
            'price_per_kg' => 'decimal:2',
            'empty_spool_weight_grams' => 'decimal:2',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function calibration(): HasOne
    {
        return $this->hasOne(Calibration::class);
    }
}
