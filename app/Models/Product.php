<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'description', 'material', 'filament_id',
        'color_name', 'color_hex', 'weight_grams',
        'print_time_minutes', 'price', 'image_path',
        'model_file_path', 'model_file_name', 'is_active'
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'weight_grams' => 'decimal:2',
            'price' => 'decimal:2',
            'print_time_minutes' => 'integer',
        ];
    }

    public function filament(): BelongsTo
    {
        return $this->belongsTo(Filament::class);
    }
}
