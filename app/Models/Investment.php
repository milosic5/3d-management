<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Investment extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'filament_id', 'name', 'amount', 'quantity', 'unit',
        'unit_cost', 'invested_at', 'notes', 'created_by',
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

    public function filament(): BelongsTo
    {
        return $this->belongsTo(Filament::class);
    }

    /**
     * A filament's price per kg follows the unit cost of its latest purchase.
     */
    public static function syncFilamentPrice(?int $filamentId): void
    {
        $filament = $filamentId ? Filament::find($filamentId) : null;
        if (! $filament) {
            return;
        }

        $latest = self::where('filament_id', $filament->id)
            ->latest('invested_at')
            ->first();

        if ($latest && $latest->unit_cost > 0) {
            $filament->updateQuietly(['price_per_kg' => $latest->unit_cost]);
        }
    }

    protected static function booted()
    {
        static::saved(function (Investment $investment) {
            self::syncFilamentPrice($investment->filament_id);

            // Moved to another filament: the previous one falls back to its own latest purchase.
            $previous = $investment->getOriginal('filament_id');
            if ($previous && $previous !== $investment->filament_id) {
                self::syncFilamentPrice($previous);
            }
        });

        static::deleted(fn (Investment $investment) => self::syncFilamentPrice($investment->filament_id));
    }
}
