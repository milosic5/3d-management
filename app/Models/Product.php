<?php

namespace App\Models;

use App\Enums\FilamentMaterial;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'description', 'material', 'filament_id',
        'color_name', 'color_hex', 'weight_grams',
        'print_time_minutes', 'price', 'image_path',
        'model_file_path', 'model_file_name',
        'model_file_path_2', 'model_file_name_2', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'material' => FilamentMaterial::class,
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
