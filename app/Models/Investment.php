<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Investment extends Model
{
    protected $fillable = [
        'category_id', 'name', 'amount', 'quantity', 'unit',
        'unit_cost', 'invested_at', 'notes', 'created_by'
    ];

    protected function casts(): array
    {
        return [
            'invested_at' => 'date',
            'amount' => 'decimal:2',
            'quantity' => 'decimal:3',
            'unit_cost' => 'decimal:4',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(InvestmentCategory::class, 'category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function booted()
    {
        $updateFilamentPrice = function (Investment $investment) {
            $filament = Filament::where('name', $investment->name)->first();
            if ($filament) {
                $latest = self::where('name', $filament->name)
                    ->latest('invested_at')
                    ->first();
                
                if ($latest && $latest->unit_cost > 0) {
                    $filament->updateQuietly(['price_per_kg' => $latest->unit_cost]);
                }
            }
        };

        static::saved($updateFilamentPrice);
        static::deleted($updateFilamentPrice);
    }
}
