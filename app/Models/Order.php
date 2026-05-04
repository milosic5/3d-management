<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'customer_name', 'status',
        'notes', 'total_price', 'estimated_print_minutes',
        'created_by', 'updated_by', 'created_at'
    ];

    protected function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
            'estimated_print_minutes' => 'integer',
        ];
    }

    protected static function booted()
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $dateToUse = $order->created_at ? \Carbon\Carbon::parse($order->created_at) : now();
                $prefix = 'ORD-' . $dateToUse->format('Ym') . '-';
                
                DB::transaction(function () use ($order, $prefix) {
                    $lastOrder = Order::where('order_number', 'like', $prefix . '%')
                        ->lockForUpdate()
                        ->orderBy('id', 'desc')
                        ->first();
                    
                    $nextSequence = 1;
                    if ($lastOrder) {
                        $lastSequence = (int) substr($lastOrder->order_number, -4);
                        $nextSequence = $lastSequence + 1;
                    }
                    
                    $order->order_number = $prefix . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
                });
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
