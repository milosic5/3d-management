<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'filament_id', 'quantity', 'unit_price',
        'print_cost', 'print_time_minutes', 'weight_grams', 'color_name',
        'color_hex', 'notes'
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'print_cost' => 'decimal:2',
            'weight_grams' => 'decimal:2',
            'print_time_minutes' => 'integer',
            'quantity' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
